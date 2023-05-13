<?php
namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
abstract class MorphOneOrMany extends HasOneOrMany
{
    protected $morphType;
    protected $morphClass;
    public function __construct(Builder $query, Model $parent, $type, $id, $localKey)
    {
        $this->morphType = $type;
        $this->morphClass = $parent->getMorphClass();
        parent::__construct($query, $parent, $id, $localKey);
    }
    public function addConstraints()
    {
        if (static::$constraints) {
            parent::addConstraints();
            $this->query->where($this->morphType, $this->morphClass);
        }
    }
    public function addEagerConstraints(array $models)
    {
        parent::addEagerConstraints($models);
        $this->query->where($this->morphType, $this->morphClass);
    }
    protected function setForeignAttributesForCreate(Model $model)
    {
        $model->{$this->getForeignKeyName()} = $this->getParentKey();
        $model->{$this->getMorphType()} = $this->morphClass;
    }
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns)->where(
            $this->morphType, $this->morphClass
        );
    }
    public function getQualifiedMorphType()
    {
        return $this->morphType;
    }
    public function getMorphType()
    {
        return last(explode('.', $this->morphType));
    }
    public function getMorphClass()
    {
        return $this->morphClass;
    }
}
