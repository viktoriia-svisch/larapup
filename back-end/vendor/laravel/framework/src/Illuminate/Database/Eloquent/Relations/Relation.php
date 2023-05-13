<?php
namespace Illuminate\Database\Eloquent\Relations;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
abstract class Relation
{
    use ForwardsCalls, Macroable {
        __call as macroCall;
    }
    protected $query;
    protected $parent;
    protected $related;
    protected static $constraints = true;
    public static $morphMap = [];
    public function __construct(Builder $query, Model $parent)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();
        $this->addConstraints();
    }
    public static function noConstraints(Closure $callback)
    {
        $previous = static::$constraints;
        static::$constraints = false;
        try {
            return call_user_func($callback);
        } finally {
            static::$constraints = $previous;
        }
    }
    abstract public function addConstraints();
    abstract public function addEagerConstraints(array $models);
    abstract public function initRelation(array $models, $relation);
    abstract public function match(array $models, Collection $results, $relation);
    abstract public function getResults();
    public function getEager()
    {
        return $this->get();
    }
    public function get($columns = ['*'])
    {
        return $this->query->get($columns);
    }
    public function touch()
    {
        $model = $this->getRelated();
        if (! $model::isIgnoringTouch()) {
            $this->rawUpdate([
                $model->getUpdatedAtColumn() => $model->freshTimestampString(),
            ]);
        }
    }
    public function rawUpdate(array $attributes = [])
    {
        return $this->query->withoutGlobalScopes()->update($attributes);
    }
    public function getRelationExistenceCountQuery(Builder $query, Builder $parentQuery)
    {
        return $this->getRelationExistenceQuery(
            $query, $parentQuery, new Expression('count(*)')
        )->setBindings([], 'select');
    }
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(), '=', $this->getExistenceCompareKey()
        );
    }
    protected function getKeys(array $models, $key = null)
    {
        return collect($models)->map(function ($value) use ($key) {
            return $key ? $value->getAttribute($key) : $value->getKey();
        })->values()->unique(null, true)->sort()->all();
    }
    public function getQuery()
    {
        return $this->query;
    }
    public function getBaseQuery()
    {
        return $this->query->getQuery();
    }
    public function getParent()
    {
        return $this->parent;
    }
    public function getQualifiedParentKeyName()
    {
        return $this->parent->getQualifiedKeyName();
    }
    public function getRelated()
    {
        return $this->related;
    }
    public function createdAt()
    {
        return $this->parent->getCreatedAtColumn();
    }
    public function updatedAt()
    {
        return $this->parent->getUpdatedAtColumn();
    }
    public function relatedUpdatedAt()
    {
        return $this->related->getUpdatedAtColumn();
    }
    protected function whereInMethod(Model $model, $key)
    {
        return $model->getKeyName() === last(explode('.', $key))
                    && $model->getIncrementing()
                    && in_array($model->getKeyType(), ['int', 'integer'])
                        ? 'whereIntegerInRaw'
                        : 'whereIn';
    }
    public static function morphMap(array $map = null, $merge = true)
    {
        $map = static::buildMorphMapFromModels($map);
        if (is_array($map)) {
            static::$morphMap = $merge && static::$morphMap
                            ? $map + static::$morphMap : $map;
        }
        return static::$morphMap;
    }
    protected static function buildMorphMapFromModels(array $models = null)
    {
        if (is_null($models) || Arr::isAssoc($models)) {
            return $models;
        }
        return array_combine(array_map(function ($model) {
            return (new $model)->getTable();
        }, $models), $models);
    }
    public static function getMorphedModel($alias)
    {
        return self::$morphMap[$alias] ?? null;
    }
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        $result = $this->forwardCallTo($this->query, $method, $parameters);
        if ($result === $this->query) {
            return $this;
        }
        return $result;
    }
    public function __clone()
    {
        $this->query = clone $this->query;
    }
}
