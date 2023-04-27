<?php
namespace Illuminate\Database\Eloquent;
use Closure;
use Exception;
use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
class Builder
{
    use BuildsQueries, Concerns\QueriesRelationships, ForwardsCalls;
    protected $query;
    protected $model;
    protected $eagerLoad = [];
    protected static $macros = [];
    protected $localMacros = [];
    protected $onDelete;
    protected $passthru = [
        'insert', 'insertGetId', 'getBindings', 'toSql',
        'exists', 'doesntExist', 'count', 'min', 'max', 'avg', 'average', 'sum', 'getConnection',
    ];
    protected $scopes = [];
    protected $removedScopes = [];
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }
    public function make(array $attributes = [])
    {
        return $this->newModelInstance($attributes);
    }
    public function withGlobalScope($identifier, $scope)
    {
        $this->scopes[$identifier] = $scope;
        if (method_exists($scope, 'extend')) {
            $scope->extend($this);
        }
        return $this;
    }
    public function withoutGlobalScope($scope)
    {
        if (! is_string($scope)) {
            $scope = get_class($scope);
        }
        unset($this->scopes[$scope]);
        $this->removedScopes[] = $scope;
        return $this;
    }
    public function withoutGlobalScopes(array $scopes = null)
    {
        if (! is_array($scopes)) {
            $scopes = array_keys($this->scopes);
        }
        foreach ($scopes as $scope) {
            $this->withoutGlobalScope($scope);
        }
        return $this;
    }
    public function removedScopes()
    {
        return $this->removedScopes;
    }
    public function whereKey($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereIn($this->model->getQualifiedKeyName(), $id);
            return $this;
        }
        return $this->where($this->model->getQualifiedKeyName(), '=', $id);
    }
    public function whereKeyNot($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereNotIn($this->model->getQualifiedKeyName(), $id);
            return $this;
        }
        return $this->where($this->model->getQualifiedKeyName(), '!=', $id);
    }
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure) {
            $column($query = $this->model->newModelQuery());
            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        } else {
            $this->query->where(...func_get_args());
        }
        return $this;
    }
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->query->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->where($column, $operator, $value, 'or');
    }
    public function latest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }
        $this->query->latest($column);
        return $this;
    }
    public function oldest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }
        $this->query->oldest($column);
        return $this;
    }
    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();
        return $instance->newCollection(array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items));
    }
    public function fromQuery($query, $bindings = [])
    {
        return $this->hydrate(
            $this->query->getConnection()->select($query, $bindings)
        );
    }
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }
        return $this->whereKey($id)->first($columns);
    }
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }
        return $this->whereKey($ids)->get($columns);
    }
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);
        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }
        throw (new ModelNotFoundException)->setModel(
            get_class($this->model), $id
        );
    }
    public function findOrNew($id, $columns = ['*'])
    {
        if (! is_null($model = $this->find($id, $columns))) {
            return $model;
        }
        return $this->newModelInstance();
    }
    public function firstOrNew(array $attributes, array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }
        return $this->newModelInstance($attributes + $values);
    }
    public function firstOrCreate(array $attributes, array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }
        return tap($this->newModelInstance($attributes + $values), function ($instance) {
            $instance->save();
        });
    }
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return tap($this->firstOrNew($attributes), function ($instance) use ($values) {
            $instance->fill($values)->save();
        });
    }
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }
        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }
    public function firstOr($columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;
            $columns = ['*'];
        }
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }
        return call_user_func($callback);
    }
    public function value($column)
    {
        if ($result = $this->first([$column])) {
            return $result->{$column};
        }
    }
    public function get($columns = ['*'])
    {
        $builder = $this->applyScopes();
        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        return $builder->getModel()->newCollection($models);
    }
    public function getModels($columns = ['*'])
    {
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            if (strpos($name, '.') === false) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }
        return $models;
    }
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        $relation = $this->getRelation($name);
        $relation->addEagerConstraints($models);
        $constraints($relation);
        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEager(), $name
        );
    }
    public function getRelation($name)
    {
        $relation = Relation::noConstraints(function () use ($name) {
            try {
                return $this->getModel()->newInstance()->$name();
            } catch (BadMethodCallException $e) {
                throw RelationNotFoundException::make($this->getModel(), $name);
            }
        });
        $nested = $this->relationsNestedUnder($name);
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }
        return $relation;
    }
    protected function relationsNestedUnder($relation)
    {
        $nested = [];
        foreach ($this->eagerLoad as $name => $constraints) {
            if ($this->isNestedUnder($relation, $name)) {
                $nested[substr($name, strlen($relation.'.'))] = $constraints;
            }
        }
        return $nested;
    }
    protected function isNestedUnder($relation, $name)
    {
        return Str::contains($name, '.') && Str::startsWith($name, $relation.'.');
    }
    public function cursor()
    {
        foreach ($this->applyScopes()->query->cursor() as $record) {
            yield $this->model->newFromBuilder($record);
        }
    }
    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        $column = is_null($column) ? $this->getModel()->getKeyName() : $column;
        $alias = is_null($alias) ? $column : $alias;
        $lastId = null;
        do {
            $clone = clone $this;
            $results = $clone->forPageAfterId($count, $lastId, $column)->get();
            $countResults = $results->count();
            if ($countResults == 0) {
                break;
            }
            if ($callback($results) === false) {
                return false;
            }
            $lastId = $results->last()->{$alias};
            unset($results);
        } while ($countResults == $count);
        return true;
    }
    protected function enforceOrderBy()
    {
        if (empty($this->query->orders) && empty($this->query->unionOrders)) {
            $this->orderBy($this->model->getQualifiedKeyName(), 'asc');
        }
    }
    public function pluck($column, $key = null)
    {
        $results = $this->toBase()->pluck($column, $key);
        if (! $this->model->hasGetMutator($column) &&
            ! $this->model->hasCast($column) &&
            ! in_array($column, $this->model->getDates())) {
            return $results;
        }
        return $results->map(function ($value) use ($column) {
            return $this->model->newFromBuilder([$column => $value])->{$column};
        });
    }
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $results = ($total = $this->toBase()->getCountForPagination())
                                    ? $this->forPage($page, $perPage)->get($columns)
                                    : $this->model->newCollection();
        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $this->skip(($page - 1) * $perPage)->take($perPage + 1);
        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }
    public function forceCreate(array $attributes)
    {
        return $this->model->unguarded(function () use ($attributes) {
            return $this->newModelInstance()->create($attributes);
        });
    }
    public function update(array $values)
    {
        return $this->toBase()->update($this->addUpdatedAtColumn($values));
    }
    public function increment($column, $amount = 1, array $extra = [])
    {
        return $this->toBase()->increment(
            $column, $amount, $this->addUpdatedAtColumn($extra)
        );
    }
    public function decrement($column, $amount = 1, array $extra = [])
    {
        return $this->toBase()->decrement(
            $column, $amount, $this->addUpdatedAtColumn($extra)
        );
    }
    protected function addUpdatedAtColumn(array $values)
    {
        if (! $this->model->usesTimestamps()) {
            return $values;
        }
        return Arr::add(
            $values, $this->model->getUpdatedAtColumn(),
            $this->model->freshTimestampString()
        );
    }
    public function delete()
    {
        if (isset($this->onDelete)) {
            return call_user_func($this->onDelete, $this);
        }
        return $this->toBase()->delete();
    }
    public function forceDelete()
    {
        return $this->query->delete();
    }
    public function onDelete(Closure $callback)
    {
        $this->onDelete = $callback;
    }
    public function scopes(array $scopes)
    {
        $builder = $this;
        foreach ($scopes as $scope => $parameters) {
            if (is_int($scope)) {
                [$scope, $parameters] = [$parameters, []];
            }
            $builder = $builder->callScope(
                [$this->model, 'scope'.ucfirst($scope)],
                (array) $parameters
            );
        }
        return $builder;
    }
    public function applyScopes()
    {
        if (! $this->scopes) {
            return $this;
        }
        $builder = clone $this;
        foreach ($this->scopes as $identifier => $scope) {
            if (! isset($builder->scopes[$identifier])) {
                continue;
            }
            $builder->callScope(function (Builder $builder) use ($scope) {
                if ($scope instanceof Closure) {
                    $scope($builder);
                }
                if ($scope instanceof Scope) {
                    $scope->apply($builder, $this->getModel());
                }
            });
        }
        return $builder;
    }
    protected function callScope(callable $scope, $parameters = [])
    {
        array_unshift($parameters, $this);
        $query = $this->getQuery();
        $originalWhereCount = is_null($query->wheres)
                    ? 0 : count($query->wheres);
        $result = $scope(...array_values($parameters)) ?? $this;
        if (count((array) $query->wheres) > $originalWhereCount) {
            $this->addNewWheresWithinGroup($query, $originalWhereCount);
        }
        return $result;
    }
    protected function addNewWheresWithinGroup(QueryBuilder $query, $originalWhereCount)
    {
        $allWheres = $query->wheres;
        $query->wheres = [];
        $this->groupWhereSliceForScope(
            $query, array_slice($allWheres, 0, $originalWhereCount)
        );
        $this->groupWhereSliceForScope(
            $query, array_slice($allWheres, $originalWhereCount)
        );
    }
    protected function groupWhereSliceForScope(QueryBuilder $query, $whereSlice)
    {
        $whereBooleans = collect($whereSlice)->pluck('boolean');
        if ($whereBooleans->contains('or')) {
            $query->wheres[] = $this->createNestedWhere(
                $whereSlice, $whereBooleans->first()
            );
        } else {
            $query->wheres = array_merge($query->wheres, $whereSlice);
        }
    }
    protected function createNestedWhere($whereSlice, $boolean = 'and')
    {
        $whereGroup = $this->getQuery()->forNestedWhere();
        $whereGroup->wheres = $whereSlice;
        return ['type' => 'Nested', 'query' => $whereGroup, 'boolean' => $boolean];
    }
    public function with($relations)
    {
        $eagerLoad = $this->parseWithRelations(is_string($relations) ? func_get_args() : $relations);
        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);
        return $this;
    }
    public function without($relations)
    {
        $this->eagerLoad = array_diff_key($this->eagerLoad, array_flip(
            is_string($relations) ? func_get_args() : $relations
        ));
        return $this;
    }
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }
    protected function parseWithRelations(array $relations)
    {
        $results = [];
        foreach ($relations as $name => $constraints) {
            if (is_numeric($name)) {
                $name = $constraints;
                [$name, $constraints] = Str::contains($name, ':')
                            ? $this->createSelectWithConstraint($name)
                            : [$name, function () {
                            }];
            }
            $results = $this->addNestedWiths($name, $results);
            $results[$name] = $constraints;
        }
        return $results;
    }
    protected function createSelectWithConstraint($name)
    {
        return [explode(':', $name)[0], function ($query) use ($name) {
            $query->select(explode(',', explode(':', $name)[1]));
        }];
    }
    protected function addNestedWiths($name, $results)
    {
        $progress = [];
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;
            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] = function () {
                };
            }
        }
        return $results;
    }
    public function getQuery()
    {
        return $this->query;
    }
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }
    public function toBase()
    {
        return $this->applyScopes()->getQuery();
    }
    public function getEagerLoads()
    {
        return $this->eagerLoad;
    }
    public function setEagerLoads(array $eagerLoad)
    {
        $this->eagerLoad = $eagerLoad;
        return $this;
    }
    public function getModel()
    {
        return $this->model;
    }
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->query->from($model->getTable());
        return $this;
    }
    public function qualifyColumn($column)
    {
        return $this->model->qualifyColumn($column);
    }
    public function getMacro($name)
    {
        return Arr::get($this->localMacros, $name);
    }
    public function __get($key)
    {
        if ($key === 'orWhere') {
            return new HigherOrderBuilderProxy($this, $key);
        }
        throw new Exception("Property [{$key}] does not exist on the Eloquent builder instance.");
    }
    public function __call($method, $parameters)
    {
        if ($method === 'macro') {
            $this->localMacros[$parameters[0]] = $parameters[1];
            return;
        }
        if (isset($this->localMacros[$method])) {
            array_unshift($parameters, $this);
            return $this->localMacros[$method](...$parameters);
        }
        if (isset(static::$macros[$method])) {
            if (static::$macros[$method] instanceof Closure) {
                return call_user_func_array(static::$macros[$method]->bindTo($this, static::class), $parameters);
            }
            return call_user_func_array(static::$macros[$method], $parameters);
        }
        if (method_exists($this->model, $scope = 'scope'.ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }
        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }
        $this->forwardCallTo($this->query, $method, $parameters);
        return $this;
    }
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'macro') {
            static::$macros[$parameters[0]] = $parameters[1];
            return;
        }
        if (! isset(static::$macros[$method])) {
            static::throwBadMethodCallException($method);
        }
        if (static::$macros[$method] instanceof Closure) {
            return call_user_func_array(Closure::bind(static::$macros[$method], null, static::class), $parameters);
        }
        return call_user_func_array(static::$macros[$method], $parameters);
    }
    public function __clone()
    {
        $this->query = clone $this->query;
    }
}
