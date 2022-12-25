<?php
namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Collection;
class MorphMany extends MorphOneOrMany
{
    public function getResults()
    {
        return ! is_null($this->getParentKey())
                ? $this->query->get()
                : $this->related->newCollection();
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
        return $this->matchMany($models, $results, $relation);
    }
}
