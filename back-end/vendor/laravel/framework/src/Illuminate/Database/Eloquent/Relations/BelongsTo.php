<?php
namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
class BelongsTo extends Relation
{
    use SupportsDefaultModels;
    protected $child;
    protected $foreignKey;
    protected $ownerKey;
    protected $relation;
    protected static $selfJoinCount = 0;
    public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        $this->ownerKey = $ownerKey;
        $this->relation = $relation;
        $this->foreignKey = $foreignKey;
        $this->child = $child;
        parent::__construct($query, $child);
    }
    public function getResults()
    {
        if (is_null($this->child->{$this->foreignKey})) {
            return $this->getDefaultFor($this->parent);
        }
        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }
    public function addConstraints()
    {
        if (static::$constraints) {
            $table = $this->related->getTable();
            $this->query->where($table.'.'.$this->ownerKey, '=', $this->child->{$this->foreignKey});
        }
    }
    public function addEagerConstraints(array $models)
    {
        $key = $this->related->getTable().'.'.$this->ownerKey;
        $whereIn = $this->whereInMethod($this->related, $this->ownerKey);
        $this->query->{$whereIn}($key, $this->getEagerModelKeys($models));
    }
    protected function getEagerModelKeys(array $models)
    {
        $keys = [];
        foreach ($models as $model) {
            if (! is_null($value = $model->{$this->foreignKey})) {
                $keys[] = $value;
            }
        }
        sort($keys);
        return array_values(array_unique($keys));
    }
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }
        return $models;
    }
    public function match(array $models, Collection $results, $relation)
    {
        $foreign = $this->foreignKey;
        $owner = $this->ownerKey;
        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->getAttribute($owner)] = $result;
        }
        foreach ($models as $model) {
            if (isset($dictionary[$model->{$foreign}])) {
                $model->setRelation($relation, $dictionary[$model->{$foreign}]);
            }
        }
        return $models;
    }
    public function update(array $attributes)
    {
        return $this->getResults()->fill($attributes)->save();
    }
    public function associate($model)
    {
        $ownerKey = $model instanceof Model ? $model->getAttribute($this->ownerKey) : $model;
        $this->child->setAttribute($this->foreignKey, $ownerKey);
        if ($model instanceof Model) {
            $this->child->setRelation($this->relation, $model);
        }
        return $this->child;
    }
    public function dissociate()
    {
        $this->child->setAttribute($this->foreignKey, null);
        return $this->child->setRelation($this->relation, null);
    }
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }
        return $query->select($columns)->whereColumn(
            $this->getQualifiedForeignKey(), '=', $query->qualifyColumn($this->ownerKey)
        );
    }
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns)->from(
            $query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash()
        );
        $query->getModel()->setTable($hash);
        return $query->whereColumn(
            $hash.'.'.$this->ownerKey, '=', $this->getQualifiedForeignKey()
        );
    }
    public function getRelationCountHash()
    {
        return 'laravel_reserved_'.static::$selfJoinCount++;
    }
    protected function relationHasIncrementingId()
    {
        return $this->related->getIncrementing() &&
                                $this->related->getKeyType() === 'int';
    }
    protected function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
    public function getQualifiedForeignKey()
    {
        return $this->child->qualifyColumn($this->foreignKey);
    }
    public function getOwnerKey()
    {
        return $this->ownerKey;
    }
    public function getQualifiedOwnerKeyName()
    {
        return $this->related->qualifyColumn($this->ownerKey);
    }
    public function getRelation()
    {
        return $this->relation;
    }
}
