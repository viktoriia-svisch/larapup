<?php
namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class HasManyThrough extends Relation
{
    protected $throughParent;
    protected $farParent;
    protected $firstKey;
    protected $secondKey;
    protected $localKey;
    protected $secondLocalKey;
    protected static $selfJoinCount = 0;
    public function __construct(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;
        $this->throughParent = $throughParent;
        $this->secondLocalKey = $secondLocalKey;
        parent::__construct($query, $throughParent);
    }
    public function addConstraints()
    {
        $localValue = $this->farParent[$this->localKey];
        $this->performJoin();
        if (static::$constraints) {
            $this->query->where($this->getQualifiedFirstKeyName(), '=', $localValue);
        }
    }
    protected function performJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;
        $farKey = $this->getQualifiedFarKeyName();
        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $farKey);
        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }
    }
    public function getQualifiedParentKeyName()
    {
        return $this->parent->qualifyColumn($this->secondLocalKey);
    }
    public function throughParentSoftDeletes()
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->throughParent));
    }
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->farParent, $this->localKey);
        $this->query->{$whereIn}(
            $this->getQualifiedFirstKeyName(), $this->getKeys($models, $this->localKey)
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
            if (isset($dictionary[$key = $model->getAttribute($this->localKey)])) {
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
            $dictionary[$result->{$this->firstKey}][] = $result;
        }
        return $dictionary;
    }
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);
        }
        return $instance;
    }
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = $this->firstOrNew($attributes);
        $instance->fill($values)->save();
        return $instance;
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
    public function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }
        return $this->where(
            $this->getRelated()->getQualifiedKeyName(), '=', $id
        )->first($columns);
    }
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }
        return $this->whereIn(
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
    public function getResults()
    {
        return ! is_null($this->farParent->{$this->localKey})
                ? $this->get()
                : $this->related->newCollection();
    }
    public function get($columns = ['*'])
    {
        $builder = $this->prepareQueryBuilder($columns);
        $models = $builder->getModels();
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        return $this->related->newCollection($models);
    }
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));
        return $this->query->paginate($perPage, $columns, $pageName, $page);
    }
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));
        return $this->query->simplePaginate($perPage, $columns, $pageName, $page);
    }
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }
        return array_merge($columns, [$this->getQualifiedFirstKeyName()]);
    }
    public function chunk($count, callable $callback)
    {
        return $this->prepareQueryBuilder()->chunk($count, $callback);
    }
    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        $column = $column ?? $this->getRelated()->getQualifiedKeyName();
        $alias = $alias ?? $this->getRelated()->getKeyName();
        return $this->prepareQueryBuilder()->chunkById($count, $callback, $column, $alias);
    }
    public function cursor()
    {
        return $this->prepareQueryBuilder()->cursor();
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
    protected function prepareQueryBuilder($columns = ['*'])
    {
        $builder = $this->query->applyScopes();
        return $builder->addSelect(
            $this->shouldSelect($builder->getQuery()->columns ? [] : $columns)
        );
    }
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from === $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }
        if ($parentQuery->getQuery()->from === $this->throughParent->getTable()) {
            return $this->getRelationExistenceQueryForThroughSelfRelation($query, $parentQuery, $columns);
        }
        $this->performJoin($query);
        return $query->select($columns)->whereColumn(
            $this->getQualifiedLocalKeyName(), '=', $this->getQualifiedFirstKeyName()
        );
    }
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());
        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $hash.'.'.$this->secondKey);
        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }
        $query->getModel()->setTable($hash);
        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$this->localKey, '=', $this->getQualifiedFirstKeyName()
        );
    }
    public function getRelationExistenceQueryForThroughSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $table = $this->throughParent->getTable().' as '.$hash = $this->getRelationCountHash();
        $query->join($table, $hash.'.'.$this->secondLocalKey, '=', $this->getQualifiedFarKeyName());
        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($hash.'.'.$this->throughParent->getDeletedAtColumn());
        }
        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$this->localKey, '=', $hash.'.'.$this->firstKey
        );
    }
    public function getRelationCountHash()
    {
        return 'laravel_reserved_'.static::$selfJoinCount++;
    }
    public function getQualifiedFarKeyName()
    {
        return $this->getQualifiedForeignKeyName();
    }
    public function getFirstKeyName()
    {
        return $this->firstKey;
    }
    public function getQualifiedFirstKeyName()
    {
        return $this->throughParent->qualifyColumn($this->firstKey);
    }
    public function getForeignKeyName()
    {
        return $this->secondKey;
    }
    public function getQualifiedForeignKeyName()
    {
        return $this->related->qualifyColumn($this->secondKey);
    }
    public function getLocalKeyName()
    {
        return $this->localKey;
    }
    public function getQualifiedLocalKeyName()
    {
        return $this->farParent->qualifyColumn($this->localKey);
    }
    public function getSecondLocalKeyName()
    {
        return $this->secondLocalKey;
    }
}
