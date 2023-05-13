<?php
namespace Illuminate\Database\Eloquent;
use Exception;
use ArrayAccess;
use JsonSerializable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
abstract class Model implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable
{
    use Concerns\HasAttributes,
        Concerns\HasEvents,
        Concerns\HasGlobalScopes,
        Concerns\HasRelationships,
        Concerns\HasTimestamps,
        Concerns\HidesAttributes,
        Concerns\GuardsAttributes,
        ForwardsCalls;
    protected $connection;
    protected $table;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $with = [];
    protected $withCount = [];
    protected $perPage = 15;
    public $exists = false;
    public $wasRecentlyCreated = false;
    protected static $resolver;
    protected static $dispatcher;
    protected static $booted = [];
    protected static $traitInitializers = [];
    protected static $globalScopes = [];
    protected static $ignoreOnTouch = [];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->initializeTraits();
        $this->syncOriginal();
        $this->fill($attributes);
    }
    protected function bootIfNotBooted()
    {
        if (! isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;
            $this->fireModelEvent('booting', false);
            static::boot();
            $this->fireModelEvent('booted', false);
        }
    }
    protected static function boot()
    {
        static::bootTraits();
    }
    protected static function bootTraits()
    {
        $class = static::class;
        $booted = [];
        static::$traitInitializers[$class] = [];
        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot'.class_basename($trait);
            if (method_exists($class, $method) && ! in_array($method, $booted)) {
                forward_static_call([$class, $method]);
                $booted[] = $method;
            }
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                static::$traitInitializers[$class][] = $method;
                static::$traitInitializers[$class] = array_unique(
                    static::$traitInitializers[$class]
                );
            }
        }
    }
    protected function initializeTraits()
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }
    public static function clearBootedModels()
    {
        static::$booted = [];
        static::$globalScopes = [];
    }
    public static function withoutTouching(callable $callback)
    {
        static::withoutTouchingOn([static::class], $callback);
    }
    public static function withoutTouchingOn(array $models, callable $callback)
    {
        static::$ignoreOnTouch = array_values(array_merge(static::$ignoreOnTouch, $models));
        try {
            call_user_func($callback);
        } finally {
            static::$ignoreOnTouch = array_values(array_diff(static::$ignoreOnTouch, $models));
        }
    }
    public static function isIgnoringTouch($class = null)
    {
        $class = $class ?: static::class;
        foreach (static::$ignoreOnTouch as $ignoredClass) {
            if ($class === $ignoredClass || is_subclass_of($class, $ignoredClass)) {
                return true;
            }
        }
        return false;
    }
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $key = $this->removeTableFromKey($key);
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }
        }
        return $this;
    }
    public function forceFill(array $attributes)
    {
        return static::unguarded(function () use ($attributes) {
            return $this->fill($attributes);
        });
    }
    public function qualifyColumn($column)
    {
        if (Str::contains($column, '.')) {
            return $column;
        }
        return $this->getTable().'.'.$column;
    }
    protected function removeTableFromKey($key)
    {
        return Str::contains($key, '.') ? last(explode('.', $key)) : $key;
    }
    public function newInstance($attributes = [], $exists = false)
    {
        $model = new static((array) $attributes);
        $model->exists = $exists;
        $model->setConnection(
            $this->getConnectionName()
        );
        $model->setTable($this->getTable());
        return $model;
    }
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance([], true);
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->getConnectionName());
        $model->fireModelEvent('retrieved', false);
        return $model;
    }
    public static function on($connection = null)
    {
        $instance = new static;
        $instance->setConnection($connection);
        return $instance->newQuery();
    }
    public static function onWriteConnection()
    {
        $instance = new static;
        return $instance->newQuery()->useWritePdo();
    }
    public static function all($columns = ['*'])
    {
        return (new static)->newQuery()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }
    public static function with($relations)
    {
        return (new static)->newQuery()->with(
            is_string($relations) ? func_get_args() : $relations
        );
    }
    public function load($relations)
    {
        $query = $this->newQueryWithoutRelationships()->with(
            is_string($relations) ? func_get_args() : $relations
        );
        $query->eagerLoadRelations([$this]);
        return $this;
    }
    public function loadMissing($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;
        $this->newCollection([$this])->loadMissing($relations);
        return $this;
    }
    protected function increment($column, $amount = 1, array $extra = [])
    {
        return $this->incrementOrDecrement($column, $amount, $extra, 'increment');
    }
    protected function decrement($column, $amount = 1, array $extra = [])
    {
        return $this->incrementOrDecrement($column, $amount, $extra, 'decrement');
    }
    protected function incrementOrDecrement($column, $amount, $extra, $method)
    {
        $query = $this->newQueryWithoutRelationships();
        if (! $this->exists) {
            return $query->{$method}($column, $amount, $extra);
        }
        $this->incrementOrDecrementAttributeValue($column, $amount, $extra, $method);
        return $query->where(
            $this->getKeyName(), $this->getKey()
        )->{$method}($column, $amount, $extra);
    }
    protected function incrementOrDecrementAttributeValue($column, $amount, $extra, $method)
    {
        $this->{$column} = $this->{$column} + ($method === 'increment' ? $amount : $amount * -1);
        $this->forceFill($extra);
        $this->syncOriginalAttribute($column);
    }
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }
        return $this->fill($attributes)->save($options);
    }
    public function push()
    {
        if (! $this->save()) {
            return false;
        }
        foreach ($this->relations as $models) {
            $models = $models instanceof Collection
                        ? $models->all() : [$models];
            foreach (array_filter($models) as $model) {
                if (! $model->push()) {
                    return false;
                }
            }
        }
        return true;
    }
    public function save(array $options = [])
    {
        $query = $this->newModelQuery();
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }
        if ($this->exists) {
            $saved = $this->isDirty() ?
                        $this->performUpdate($query) : true;
        }
        else {
            $saved = $this->performInsert($query);
            if (! $this->getConnectionName() &&
                $connection = $query->getConnection()) {
                $this->setConnection($connection->getName());
            }
        }
        if ($saved) {
            $this->finishSave($options);
        }
        return $saved;
    }
    public function saveOrFail(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            return $this->save($options);
        });
    }
    protected function finishSave(array $options)
    {
        $this->fireModelEvent('saved', false);
        if ($this->isDirty() && ($options['touch'] ?? true)) {
            $this->touchOwners();
        }
        $this->syncOriginal();
    }
    protected function performUpdate(Builder $query)
    {
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }
        $dirty = $this->getDirty();
        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);
            $this->syncChanges();
            $this->fireModelEvent('updated', false);
        }
        return true;
    }
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());
        return $query;
    }
    protected function getKeyForSaveQuery()
    {
        return $this->original[$this->getKeyName()]
                        ?? $this->getKey();
    }
    protected function performInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }
        $attributes = $this->getAttributes();
        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        }
        else {
            if (empty($attributes)) {
                return true;
            }
            $query->insert($attributes);
        }
        $this->exists = true;
        $this->wasRecentlyCreated = true;
        $this->fireModelEvent('created', false);
        return true;
    }
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());
        $this->setAttribute($keyName, $id);
    }
    public static function destroy($ids)
    {
        $count = 0;
        if ($ids instanceof BaseCollection) {
            $ids = $ids->all();
        }
        $ids = is_array($ids) ? $ids : func_get_args();
        $key = ($instance = new static)->getKeyName();
        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }
        return $count;
    }
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }
        if (! $this->exists) {
            return;
        }
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }
        $this->touchOwners();
        $this->performDeleteOnModel();
        $this->fireModelEvent('deleted', false);
        return true;
    }
    public function forceDelete()
    {
        return $this->delete();
    }
    protected function performDeleteOnModel()
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->delete();
        $this->exists = false;
    }
    public static function query()
    {
        return (new static)->newQuery();
    }
    public function newQuery()
    {
        return $this->registerGlobalScopes($this->newQueryWithoutScopes());
    }
    public function newModelQuery()
    {
        return $this->newEloquentBuilder(
            $this->newBaseQueryBuilder()
        )->setModel($this);
    }
    public function newQueryWithoutRelationships()
    {
        return $this->registerGlobalScopes($this->newModelQuery());
    }
    public function registerGlobalScopes($builder)
    {
        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            $builder->withGlobalScope($identifier, $scope);
        }
        return $builder;
    }
    public function newQueryWithoutScopes()
    {
        return $this->newModelQuery()
                    ->with($this->with)
                    ->withCount($this->withCount);
    }
    public function newQueryWithoutScope($scope)
    {
        return $this->newQuery()->withoutGlobalScope($scope);
    }
    public function newQueryForRestoration($ids)
    {
        return is_array($ids)
                ? $this->newQueryWithoutScopes()->whereIn($this->getQualifiedKeyName(), $ids)
                : $this->newQueryWithoutScopes()->whereKey($ids);
    }
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();
        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
    public function newPivot(self $parent, array $attributes, $table, $exists, $using = null)
    {
        return $using ? $using::fromRawAttributes($parent, $attributes, $table, $exists)
                      : Pivot::fromAttributes($parent, $attributes, $table, $exists);
    }
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }
        return $json;
    }
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    public function fresh($with = [])
    {
        if (! $this->exists) {
            return;
        }
        return static::newQueryWithoutScopes()
                        ->with(is_string($with) ? func_get_args() : $with)
                        ->where($this->getKeyName(), $this->getKey())
                        ->first();
    }
    public function refresh()
    {
        if (! $this->exists) {
            return $this;
        }
        $this->setRawAttributes(
            static::newQueryWithoutScopes()->findOrFail($this->getKey())->attributes
        );
        $this->load(collect($this->relations)->except('pivot')->keys()->toArray());
        $this->syncOriginal();
        return $this;
    }
    public function replicate(array $except = null)
    {
        $defaults = [
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];
        $attributes = Arr::except(
            $this->attributes, $except ? array_unique(array_merge($except, $defaults)) : $defaults
        );
        return tap(new static, function ($instance) use ($attributes) {
            $instance->setRawAttributes($attributes);
            $instance->setRelations($this->relations);
        });
    }
    public function is($model)
    {
        return ! is_null($model) &&
               $this->getKey() === $model->getKey() &&
               $this->getTable() === $model->getTable() &&
               $this->getConnectionName() === $model->getConnectionName();
    }
    public function isNot($model)
    {
        return ! $this->is($model);
    }
    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }
    public function getConnectionName()
    {
        return $this->connection;
    }
    public function setConnection($name)
    {
        $this->connection = $name;
        return $this;
    }
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }
    public static function getConnectionResolver()
    {
        return static::$resolver;
    }
    public static function setConnectionResolver(Resolver $resolver)
    {
        static::$resolver = $resolver;
    }
    public static function unsetConnectionResolver()
    {
        static::$resolver = null;
    }
    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace(
                '\\', '', Str::snake(Str::plural(class_basename($this)))
            );
        }
        return $this->table;
    }
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }
    public function getKeyName()
    {
        return $this->primaryKey;
    }
    public function setKeyName($key)
    {
        $this->primaryKey = $key;
        return $this;
    }
    public function getQualifiedKeyName()
    {
        return $this->qualifyColumn($this->getKeyName());
    }
    public function getKeyType()
    {
        return $this->keyType;
    }
    public function setKeyType($type)
    {
        $this->keyType = $type;
        return $this;
    }
    public function getIncrementing()
    {
        return $this->incrementing;
    }
    public function setIncrementing($value)
    {
        $this->incrementing = $value;
        return $this;
    }
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }
    public function getQueueableId()
    {
        return $this->getKey();
    }
    public function getQueueableRelations()
    {
        $relations = [];
        foreach ($this->getRelations() as $key => $relation) {
            if (! method_exists($this, $key)) {
                continue;
            }
            $relations[] = $key;
            if ($relation instanceof QueueableCollection) {
                foreach ($relation->getQueueableRelations() as $collectionValue) {
                    $relations[] = $key.'.'.$collectionValue;
                }
            }
            if ($relation instanceof QueueableEntity) {
                foreach ($relation->getQueueableRelations() as $entityKey => $entityValue) {
                    $relations[] = $key.'.'.$entityValue;
                }
            }
        }
        return array_unique($relations);
    }
    public function getQueueableConnection()
    {
        return $this->getConnectionName();
    }
    public function getRouteKey()
    {
        return $this->getAttribute($this->getRouteKeyName());
    }
    public function getRouteKeyName()
    {
        return $this->getKeyName();
    }
    public function resolveRouteBinding($value)
    {
        return $this->where($this->getRouteKeyName(), $value)->first();
    }
    public function getForeignKey()
    {
        return Str::snake(class_basename($this)).'_'.$this->getKeyName();
    }
    public function getPerPage()
    {
        return $this->perPage;
    }
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
    public function __call($method, $parameters)
    {
        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
    public function __toString()
    {
        return $this->toJson();
    }
    public function __wakeup()
    {
        $this->bootIfNotBooted();
    }
}
