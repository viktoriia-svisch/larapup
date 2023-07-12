<?php
namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class BelongsToMany extends Relation
{
    use Concerns\InteractsWithPivotTable;
    protected $table;
    protected $foreignPivotKey;
    protected $relatedPivotKey;
    protected $parentKey;
    protected $relatedKey;
    protected $relationName;
    protected $pivotColumns = [];
    protected $pivotWheres = [];
    protected $pivotWhereIns = [];
    protected $pivotValues = [];
    public $withTimestamps = false;
    protected $pivotCreatedAt;
    protected $pivotUpdatedAt;
    protected $using;
    protected $accessor = 'pivot';
    protected static $selfJoinCount = 0;
    public function __construct(Builder $query, Model $parent, $table, $foreignPivotKey,
                                $relatedPivotKey, $parentKey, $relatedKey, $relationName = null)
    {
        $this->table = $table;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relationName = $relationName;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->foreignPivotKey = $foreignPivotKey;
        parent::__construct($query, $parent);
    }
    public function addConstraints()
    {
        $this->performJoin();
        if (static::$constraints) {
            $this->addWhereConstraints();
        }
    }
    protected function performJoin($query = null)
    {
        $query = $query ?: $this->query;
        $baseTable = $this->related->getTable();
        $key = $baseTable.'.'.$this->relatedKey;
        $query->join($this->table, $key, '=', $this->getQualifiedRelatedPivotKeyName());
        return $this;
    }
    protected function addWhereConstraints()
    {
        $this->query->where(
            $this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
        );
        return $this;
    }
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->parent, $this->parentKey);
        $this->query->{$whereIn}(
            $this->getQualifiedForeignPivotKeyName(),
            $this->getKeys($models, $this->parentKey)
        );
    }
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }
        return $models;
    }
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->{$this->parentKey}])) {
                $model->setRelation(
                    $relation, $this->related->newCollection($dictionary[$key])
                );
            }
        }
        return $models;
    }
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->{$this->accessor}->{$this->foreignPivotKey}][] = $result;
        }
        return $dictionary;
    }
    public function getPivotClass()
    {
        return $this->using ?? Pivot::class;
    }
    public function using($class)
    {
        $this->using = $class;
        return $this;
    }
    public function as($accessor)
    {
        $this->accessor = $accessor;
        return $this;
    }
    public function wherePivot($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->pivotWheres[] = func_get_args();
        return $this->where($this->table.'.'.$column, $operator, $value, $boolean);
    }
    public function wherePivotIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->pivotWhereIns[] = func_get_args();
        return $this->whereIn($this->table.'.'.$column, $values, $boolean, $not);
    }
    public function orWherePivot($column, $operator = null, $value = null)
    {
        return $this->wherePivot($column, $operator, $value, 'or');
    }
    public function withPivotValue($column, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $name => $value) {
                $this->withPivotValue($name, $value);
            }
            return $this;
        }
        if (is_null($value)) {
            throw new InvalidArgumentException('The provided value may not be null.');
        }
        $this->pivotValues[] = compact('column', 'value');
        return $this->wherePivot($column, '=', $value);
    }
    public function orWherePivotIn($column, $values)
    {
        return $this->wherePivotIn($column, $values, 'or');
    }
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();
        }
        return $instance;
    }
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);
        }
        return $instance;
    }
    public function firstOrCreate(array $attributes, array $joining = [], $touch = true)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->create($attributes, $joining, $touch);
        }
        return $instance;
    }
    public function updateOrCreate(array $attributes, array $values = [], array $joining = [], $touch = true)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            return $this->create($values, $joining, $touch);
        }
        $instance->fill($values);
        $instance->save(['touch' => false]);
        return $instance;
    }
    public function find($id, $columns = ['*'])
    {
        return is_array($id) ? $this->findMany($id, $columns) : $this->where(
            $this->getRelated()->getQualifiedKeyName(), '=', $id
        )->first($columns);
    }
    public function findMany($ids, $columns = ['*'])
    {
        return empty($ids) ? $this->getRelated()->newCollection() : $this->whereIn(
            $this->getRelated()->getQualifiedKeyName(), $ids
        )->get($columns);
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
        throw (new ModelNotFoundException)->setModel(get_class($this->related), $id);
    }
    public function first($columns = ['*'])
    {
        $results = $this->take(1)->get($columns);
        return count($results) > 0 ? $results->first() : null;
    }
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }
        throw (new ModelNotFoundException)->setModel(get_class($this->related));
    }
    public function getResults()
    {
        return ! is_null($this->parent->{$this->parentKey})
                ? $this->get()
                : $this->related->newCollection();
    }
    public function get($columns = ['*'])
    {
        $builder = $this->query->applyScopes();
        $columns = $builder->getQuery()->columns ? [] : $columns;
        $models = $builder->addSelect(
            $this->shouldSelect($columns)
        )->getModels();
        $this->hydratePivotRelation($models);
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        return $this->related->newCollection($models);
    }
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }
        return array_merge($columns, $this->aliasedPivotColumns());
    }
    protected function aliasedPivotColumns()
    {
        $defaults = [$this->foreignPivotKey, $this->relatedPivotKey];
        return collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
            return $this->table.'.'.$column.' as pivot_'.$column;
        })->unique()->all();
    }
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));
        return tap($this->query->paginate($perPage, $columns, $pageName, $page), function ($paginator) {
            $this->hydratePivotRelation($paginator->items());
        });
    }
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));
        return tap($this->query->simplePaginate($perPage, $columns, $pageName, $page), function ($paginator) {
            $this->hydratePivotRelation($paginator->items());
        });
    }
    public function chunk($count, callable $callback)
    {
        $this->query->addSelect($this->shouldSelect());
        return $this->query->chunk($count, function ($results) use ($callback) {
            $this->hydratePivotRelation($results->all());
            return $callback($results);
        });
    }
    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        $this->query->addSelect($this->shouldSelect());
        $column = $column ?? $this->getRelated()->qualifyColumn(
            $this->getRelatedKeyName()
        );
        $alias = $alias ?? $this->getRelatedKeyName();
        return $this->query->chunkById($count, function ($results) use ($callback) {
            $this->hydratePivotRelation($results->all());
            return $callback($results);
        }, $column, $alias);
    }
    public function each(callable $callback, $count = 1000)
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
        });
    }
    protected function hydratePivotRelation(array $models)
    {
        foreach ($models as $model) {
            $model->setRelation($this->accessor, $this->newExistingPivot(
                $this->migratePivotAttributes($model)
            ));
        }
    }
    protected function migratePivotAttributes(Model $model)
    {
        $values = [];
        foreach ($model->getAttributes() as $key => $value) {
            if (strpos($key, 'pivot_') === 0) {
                $values[substr($key, 6)] = $value;
                unset($model->$key);
            }
        }
        return $values;
    }
    public function touchIfTouching()
    {
        if ($this->touchingParent()) {
            $this->getParent()->touch();
        }
        if ($this->getParent()->touches($this->relationName)) {
            $this->touch();
        }
    }
    protected function touchingParent()
    {
        return $this->getRelated()->touches($this->guessInverseRelation());
    }
    protected function guessInverseRelation()
    {
        return Str::camel(Str::plural(class_basename($this->getParent())));
    }
    public function touch()
    {
        $key = $this->getRelated()->getKeyName();
        $columns = [
            $this->related->getUpdatedAtColumn() => $this->related->freshTimestampString(),
        ];
        if (count($ids = $this->allRelatedIds()) > 0) {
            $this->getRelated()->newQueryWithoutRelationships()->whereIn($key, $ids)->update($columns);
        }
    }
    public function allRelatedIds()
    {
        return $this->newPivotQuery()->pluck($this->relatedPivotKey);
    }
    public function save(Model $model, array $pivotAttributes = [], $touch = true)
    {
        $model->save(['touch' => false]);
        $this->attach($model, $pivotAttributes, $touch);
        return $model;
    }
    public function saveMany($models, array $pivotAttributes = [])
    {
        foreach ($models as $key => $model) {
            $this->save($model, (array) ($pivotAttributes[$key] ?? []), false);
        }
        $this->touchIfTouching();
        return $models;
    }
    public function create(array $attributes = [], array $joining = [], $touch = true)
    {
        $instance = $this->related->newInstance($attributes);
        $instance->save(['touch' => false]);
        $this->attach($instance, $joining, $touch);
        return $instance;
    }
    public function createMany(array $records, array $joinings = [])
    {
        $instances = [];
        foreach ($records as $key => $record) {
            $instances[] = $this->create($record, (array) ($joinings[$key] ?? []), false);
        }
        $this->touchIfTouching();
        return $instances;
    }
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfJoin($query, $parentQuery, $columns);
        }
        $this->performJoin($query);
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }
    public function getRelationExistenceQueryForSelfJoin(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns);
        $query->from($this->related->getTable().' as '.$hash = $this->getRelationCountHash());
        $this->related->setTable($hash);
        $this->performJoin($query);
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }
    public function getExistenceCompareKey()
    {
        return $this->getQualifiedForeignPivotKeyName();
    }
    public function getRelationCountHash()
    {
        return 'laravel_reserved_'.static::$selfJoinCount++;
    }
    public function withTimestamps($createdAt = null, $updatedAt = null)
    {
        $this->withTimestamps = true;
        $this->pivotCreatedAt = $createdAt;
        $this->pivotUpdatedAt = $updatedAt;
        return $this->withPivot($this->createdAt(), $this->updatedAt());
    }
    public function createdAt()
    {
        return $this->pivotCreatedAt ?: $this->parent->getCreatedAtColumn();
    }
    public function updatedAt()
    {
        return $this->pivotUpdatedAt ?: $this->parent->getUpdatedAtColumn();
    }
    public function getForeignPivotKeyName()
    {
        return $this->foreignPivotKey;
    }
    public function getQualifiedForeignPivotKeyName()
    {
        return $this->table.'.'.$this->foreignPivotKey;
    }
    public function getRelatedPivotKeyName()
    {
        return $this->relatedPivotKey;
    }
    public function getQualifiedRelatedPivotKeyName()
    {
        return $this->table.'.'.$this->relatedPivotKey;
    }
    public function getParentKeyName()
    {
        return $this->parentKey;
    }
    public function getQualifiedParentKeyName()
    {
        return $this->parent->qualifyColumn($this->parentKey);
    }
    public function getRelatedKeyName()
    {
        return $this->relatedKey;
    }
    public function getTable()
    {
        return $this->table;
    }
    public function getRelationName()
    {
        return $this->relationName;
    }
    public function getPivotAccessor()
    {
        return $this->accessor;
    }
}
