<?php
namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
abstract class HasOneOrMany extends Relation
{
    protected $foreignKey;
    protected $localKey;
    protected static $selfJoinCount = 0;
    public function __construct(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        $this->localKey = $localKey;
        $this->foreignKey = $foreignKey;
        parent::__construct($query, $parent);
    }
    public function make(array $attributes = [])
    {
        return tap($this->related->newInstance($attributes), function ($instance) {
            $this->setForeignAttributesForCreate($instance);
        });
    }
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->where($this->foreignKey, '=', $this->getParentKey());
            $this->query->whereNotNull($this->foreignKey);
        }
    }
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->parent, $this->localKey);
        $this->query->{$whereIn}(
            $this->foreignKey, $this->getKeys($models, $this->localKey)
        );
    }
    public function matchOne(array $models, Collection $results, $relation)
    {
        return $this->matchOneOrMany($models, $results, $relation, 'one');
    }
    public function matchMany(array $models, Collection $results, $relation)
    {
        return $this->matchOneOrMany($models, $results, $relation, 'many');
    }
    protected function matchOneOrMany(array $models, Collection $results, $relation, $type)
    {
        $dictionary = $this->buildDictionary($results);
        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getAttribute($this->localKey)])) {
                $model->setRelation(
                    $relation, $this->getRelationValue($dictionary, $key, $type)
                );
            }
        }
        return $models;
    }
    protected function getRelationValue(array $dictionary, $key, $type)
    {
        $value = $dictionary[$key];
        return $type === 'one' ? reset($value) : $this->related->newCollection($value);
    }
    protected function buildDictionary(Collection $results)
    {
        $foreign = $this->getForeignKeyName();
        return $results->mapToDictionary(function ($result) use ($foreign) {
            return [$result->{$foreign} => $result];
        })->all();
    }
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();
            $this->setForeignAttributesForCreate($instance);
        }
        return $instance;
    }
    public function firstOrNew(array $attributes, array $values = [])
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes + $values);
            $this->setForeignAttributesForCreate($instance);
        }
        return $instance;
    }
    public function firstOrCreate(array $attributes, array $values = [])
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->create($attributes + $values);
        }
        return $instance;
    }
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return tap($this->firstOrNew($attributes), function ($instance) use ($values) {
            $instance->fill($values);
            $instance->save();
        });
    }
    public function save(Model $model)
    {
        $this->setForeignAttributesForCreate($model);
        return $model->save() ? $model : false;
    }
    public function saveMany($models)
    {
        foreach ($models as $model) {
            $this->save($model);
        }
        return $models;
    }
    public function create(array $attributes = [])
    {
        return tap($this->related->newInstance($attributes), function ($instance) {
            $this->setForeignAttributesForCreate($instance);
            $instance->save();
        });
    }
    public function createMany(array $records)
    {
        $instances = $this->related->newCollection();
        foreach ($records as $record) {
            $instances->push($this->create($record));
        }
        return $instances;
    }
    protected function setForeignAttributesForCreate(Model $model)
    {
        $model->setAttribute($this->getForeignKeyName(), $this->getParentKey());
    }
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($query->getQuery()->from == $parentQuery->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());
        $query->getModel()->setTable($hash);
        return $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(), '=', $hash.'.'.$this->getForeignKeyName()
        );
    }
    public function getRelationCountHash()
    {
        return 'laravel_reserved_'.static::$selfJoinCount++;
    }
    public function getExistenceCompareKey()
    {
        return $this->getQualifiedForeignKeyName();
    }
    public function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }
    public function getQualifiedParentKeyName()
    {
        return $this->parent->qualifyColumn($this->localKey);
    }
    public function getForeignKeyName()
    {
        $segments = explode('.', $this->getQualifiedForeignKeyName());
        return end($segments);
    }
    public function getQualifiedForeignKeyName()
    {
        return $this->foreignKey;
    }
    public function getLocalKeyName()
    {
        return $this->localKey;
    }
}
