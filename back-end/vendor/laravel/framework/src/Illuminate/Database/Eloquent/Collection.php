<?php
namespace Illuminate\Database\Eloquent;
use LogicException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Support\Collection as BaseCollection;
class Collection extends BaseCollection implements QueueableCollection
{
    public function find($key, $default = null)
    {
        if ($key instanceof Model) {
            $key = $key->getKey();
        }
        if ($key instanceof Arrayable) {
            $key = $key->toArray();
        }
        if (is_array($key)) {
            if ($this->isEmpty()) {
                return new static;
            }
            return $this->whereIn($this->first()->getKeyName(), $key);
        }
        return Arr::first($this->items, function ($model) use ($key) {
            return $model->getKey() == $key;
        }, $default);
    }
    public function load($relations)
    {
        if ($this->isNotEmpty()) {
            if (is_string($relations)) {
                $relations = func_get_args();
            }
            $query = $this->first()->newQueryWithoutRelationships()->with($relations);
            $this->items = $query->eagerLoadRelations($this->items);
        }
        return $this;
    }
    public function loadCount($relations)
    {
        if ($this->isEmpty()) {
            return $this;
        }
        $models = $this->first()->newModelQuery()
            ->whereKey($this->modelKeys())
            ->select($this->first()->getKeyName())
            ->withCount(...func_get_args())
            ->get();
        $attributes = Arr::except(
            array_keys($models->first()->getAttributes()),
            $models->first()->getKeyName()
        );
        $models->each(function ($model) use ($attributes) {
            $this->find($model->getKey())->forceFill(
                Arr::only($model->getAttributes(), $attributes)
            )->syncOriginalAttributes($attributes);
        });
        return $this;
    }
    public function loadMissing($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }
        foreach ($relations as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $segments = explode('.', explode(':', $key)[0]);
            if (Str::contains($key, ':')) {
                $segments[count($segments) - 1] .= ':'.explode(':', $key)[1];
            }
            $path = [];
            foreach ($segments as $segment) {
                $path[] = [$segment => $segment];
            }
            if (is_callable($value)) {
                $path[count($segments) - 1][end($segments)] = $value;
            }
            $this->loadMissingRelation($this, $path);
        }
        return $this;
    }
    protected function loadMissingRelation(Collection $models, array $path)
    {
        $relation = array_shift($path);
        $name = explode(':', key($relation))[0];
        if (is_string(reset($relation))) {
            $relation = reset($relation);
        }
        $models->filter(function ($model) use ($name) {
            return ! is_null($model) && ! $model->relationLoaded($name);
        })->load($relation);
        if (empty($path)) {
            return;
        }
        $models = $models->pluck($name);
        if ($models->first() instanceof BaseCollection) {
            $models = $models->collapse();
        }
        $this->loadMissingRelation(new static($models), $path);
    }
    public function loadMorph($relation, $relations)
    {
        $this->pluck($relation)
            ->filter()
            ->groupBy(function ($model) {
                return get_class($model);
            })
            ->each(function ($models, $className) use ($relations) {
                static::make($models)->load($relations[$className] ?? []);
            });
        return $this;
    }
    public function add($item)
    {
        $this->items[] = $item;
        return $this;
    }
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() > 1 || $this->useAsCallable($key)) {
            return parent::contains(...func_get_args());
        }
        if ($key instanceof Model) {
            return parent::contains(function ($model) use ($key) {
                return $model->is($key);
            });
        }
        return parent::contains(function ($model) use ($key) {
            return $model->getKey() == $key;
        });
    }
    public function modelKeys()
    {
        return array_map(function ($model) {
            return $model->getKey();
        }, $this->items);
    }
    public function merge($items)
    {
        $dictionary = $this->getDictionary();
        foreach ($items as $item) {
            $dictionary[$item->getKey()] = $item;
        }
        return new static(array_values($dictionary));
    }
    public function map(callable $callback)
    {
        $result = parent::map($callback);
        return $result->contains(function ($item) {
            return ! $item instanceof Model;
        }) ? $result->toBase() : $result;
    }
    public function fresh($with = [])
    {
        if ($this->isEmpty()) {
            return new static;
        }
        $model = $this->first();
        $freshModels = $model->newQueryWithoutScopes()
            ->with(is_string($with) ? func_get_args() : $with)
            ->whereIn($model->getKeyName(), $this->modelKeys())
            ->get()
            ->getDictionary();
        return $this->map(function ($model) use ($freshModels) {
            return $model->exists && isset($freshModels[$model->getKey()])
                    ? $freshModels[$model->getKey()] : null;
        });
    }
    public function diff($items)
    {
        $diff = new static;
        $dictionary = $this->getDictionary($items);
        foreach ($this->items as $item) {
            if (! isset($dictionary[$item->getKey()])) {
                $diff->add($item);
            }
        }
        return $diff;
    }
    public function intersect($items)
    {
        $intersect = new static;
        $dictionary = $this->getDictionary($items);
        foreach ($this->items as $item) {
            if (isset($dictionary[$item->getKey()])) {
                $intersect->add($item);
            }
        }
        return $intersect;
    }
    public function unique($key = null, $strict = false)
    {
        if (! is_null($key)) {
            return parent::unique($key, $strict);
        }
        return new static(array_values($this->getDictionary()));
    }
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }
        $dictionary = Arr::only($this->getDictionary(), $keys);
        return new static(array_values($dictionary));
    }
    public function except($keys)
    {
        $dictionary = Arr::except($this->getDictionary(), $keys);
        return new static(array_values($dictionary));
    }
    public function makeHidden($attributes)
    {
        return $this->each->addHidden($attributes);
    }
    public function makeVisible($attributes)
    {
        return $this->each->makeVisible($attributes);
    }
    public function getDictionary($items = null)
    {
        $items = is_null($items) ? $this->items : $items;
        $dictionary = [];
        foreach ($items as $value) {
            $dictionary[$value->getKey()] = $value;
        }
        return $dictionary;
    }
    public function pluck($value, $key = null)
    {
        return $this->toBase()->pluck($value, $key);
    }
    public function keys()
    {
        return $this->toBase()->keys();
    }
    public function zip($items)
    {
        return call_user_func_array([$this->toBase(), 'zip'], func_get_args());
    }
    public function collapse()
    {
        return $this->toBase()->collapse();
    }
    public function flatten($depth = INF)
    {
        return $this->toBase()->flatten($depth);
    }
    public function flip()
    {
        return $this->toBase()->flip();
    }
    public function pad($size, $value)
    {
        return $this->toBase()->pad($size, $value);
    }
    public function getQueueableClass()
    {
        if ($this->isEmpty()) {
            return;
        }
        $class = get_class($this->first());
        $this->each(function ($model) use ($class) {
            if (get_class($model) !== $class) {
                throw new LogicException('Queueing collections with multiple model types is not supported.');
            }
        });
        return $class;
    }
    public function getQueueableIds()
    {
        if ($this->isEmpty()) {
            return [];
        }
        return $this->first() instanceof QueueableEntity
                    ? $this->map->getQueueableId()->all()
                    : $this->modelKeys();
    }
    public function getQueueableRelations()
    {
        return $this->isNotEmpty() ? $this->first()->getQueueableRelations() : [];
    }
    public function getQueueableConnection()
    {
        if ($this->isEmpty()) {
            return;
        }
        $connection = $this->first()->getConnectionName();
        $this->each(function ($model) use ($connection) {
            if ($model->getConnectionName() !== $connection) {
                throw new LogicException('Queueing collections with multiple model connections is not supported.');
            }
        });
        return $connection;
    }
}
