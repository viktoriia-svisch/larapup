<?php
namespace Illuminate\Database\Eloquent\Relations;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
class MorphTo extends BelongsTo
{
    protected $morphType;
    protected $models;
    protected $dictionary = [];
    protected $macroBuffer = [];
    public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
    {
        $this->morphType = $type;
        parent::__construct($query, $parent, $foreignKey, $ownerKey, $relation);
    }
    public function addEagerConstraints(array $models)
    {
        $this->buildDictionary($this->models = Collection::make($models));
    }
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $this->dictionary[$model->{$this->morphType}][$model->{$this->foreignKey}][] = $model;
            }
        }
    }
    public function getEager()
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchToMorphParents($type, $this->getResultsByType($type));
        }
        return $this->models;
    }
    protected function getResultsByType($type)
    {
        $instance = $this->createModelByType($type);
        $ownerKey = $this->ownerKey ?? $instance->getKeyName();
        $query = $this->replayMacros($instance->newQuery())
                            ->mergeConstraintsFrom($this->getQuery())
                            ->with($this->getQuery()->getEagerLoads());
        return $query->whereIn(
            $instance->getTable().'.'.$ownerKey, $this->gatherKeysByType($type)
        )->get();
    }
    protected function gatherKeysByType($type)
    {
        return collect($this->dictionary[$type])->map(function ($models) {
            return head($models)->{$this->foreignKey};
        })->values()->unique()->all();
    }
    public function createModelByType($type)
    {
        $class = Model::getActualClassNameForMorph($type);
        return new $class;
    }
    public function match(array $models, Collection $results, $relation)
    {
        return $models;
    }
    protected function matchToMorphParents($type, Collection $results)
    {
        foreach ($results as $result) {
            $ownerKey = ! is_null($this->ownerKey) ? $result->{$this->ownerKey} : $result->getKey();
            if (isset($this->dictionary[$type][$ownerKey])) {
                foreach ($this->dictionary[$type][$ownerKey] as $model) {
                    $model->setRelation($this->relation, $result);
                }
            }
        }
    }
    public function associate($model)
    {
        $this->parent->setAttribute(
            $this->foreignKey, $model instanceof Model ? $model->getKey() : null
        );
        $this->parent->setAttribute(
            $this->morphType, $model instanceof Model ? $model->getMorphClass() : null
        );
        return $this->parent->setRelation($this->relation, $model);
    }
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);
        $this->parent->setAttribute($this->morphType, null);
        return $this->parent->setRelation($this->relation, null);
    }
    public function touch()
    {
        if (! is_null($this->child->{$this->foreignKey})) {
            parent::touch();
        }
    }
    public function getMorphType()
    {
        return $this->morphType;
    }
    public function getDictionary()
    {
        return $this->dictionary;
    }
    protected function replayMacros(Builder $query)
    {
        foreach ($this->macroBuffer as $macro) {
            $query->{$macro['method']}(...$macro['parameters']);
        }
        return $query;
    }
    public function __call($method, $parameters)
    {
        try {
            $result = parent::__call($method, $parameters);
            if (in_array($method, ['select', 'selectRaw', 'selectSub', 'addSelect', 'withoutGlobalScopes'])) {
                $this->macroBuffer[] = compact('method', 'parameters');
            }
            return $result;
        }
        catch (BadMethodCallException $e) {
            $this->macroBuffer[] = compact('method', 'parameters');
            return $this;
        }
    }
}
