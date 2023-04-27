<?php
namespace Illuminate\Database\Eloquent;
use Faker\Generator as Faker;
use InvalidArgumentException;
use Illuminate\Support\Traits\Macroable;
class FactoryBuilder
{
    use Macroable;
    protected $definitions;
    protected $class;
    protected $name = 'default';
    protected $connection;
    protected $states;
    protected $afterMaking = [];
    protected $afterCreating = [];
    protected $activeStates = [];
    protected $faker;
    protected $amount = null;
    public function __construct($class, $name, array $definitions, array $states,
                                array $afterMaking, array $afterCreating, Faker $faker)
    {
        $this->name = $name;
        $this->class = $class;
        $this->faker = $faker;
        $this->states = $states;
        $this->definitions = $definitions;
        $this->afterMaking = $afterMaking;
        $this->afterCreating = $afterCreating;
    }
    public function times($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    public function state($state)
    {
        return $this->states([$state]);
    }
    public function states($states)
    {
        $this->activeStates = is_array($states) ? $states : func_get_args();
        return $this;
    }
    public function connection($name)
    {
        $this->connection = $name;
        return $this;
    }
    public function lazy(array $attributes = [])
    {
        return function () use ($attributes) {
            return $this->create($attributes);
        };
    }
    public function create(array $attributes = [])
    {
        $results = $this->make($attributes);
        if ($results instanceof Model) {
            $this->store(collect([$results]));
            $this->callAfterCreating(collect([$results]));
        } else {
            $this->store($results);
            $this->callAfterCreating($results);
        }
        return $results;
    }
    protected function store($results)
    {
        $results->each(function ($model) {
            if (! isset($this->connection)) {
                $model->setConnection($model->newQueryWithoutScopes()->getConnection()->getName());
            }
            $model->save();
        });
    }
    public function make(array $attributes = [])
    {
        if ($this->amount === null) {
            return tap($this->makeInstance($attributes), function ($instance) {
                $this->callAfterMaking(collect([$instance]));
            });
        }
        if ($this->amount < 1) {
            return (new $this->class)->newCollection();
        }
        $instances = (new $this->class)->newCollection(array_map(function () use ($attributes) {
            return $this->makeInstance($attributes);
        }, range(1, $this->amount)));
        $this->callAfterMaking($instances);
        return $instances;
    }
    public function raw(array $attributes = [])
    {
        if ($this->amount === null) {
            return $this->getRawAttributes($attributes);
        }
        if ($this->amount < 1) {
            return [];
        }
        return array_map(function () use ($attributes) {
            return $this->getRawAttributes($attributes);
        }, range(1, $this->amount));
    }
    protected function getRawAttributes(array $attributes = [])
    {
        if (! isset($this->definitions[$this->class][$this->name])) {
            throw new InvalidArgumentException("Unable to locate factory with name [{$this->name}] [{$this->class}].");
        }
        $definition = call_user_func(
            $this->definitions[$this->class][$this->name],
            $this->faker, $attributes
        );
        return $this->expandAttributes(
            array_merge($this->applyStates($definition, $attributes), $attributes)
        );
    }
    protected function makeInstance(array $attributes = [])
    {
        return Model::unguarded(function () use ($attributes) {
            $instance = new $this->class(
                $this->getRawAttributes($attributes)
            );
            if (isset($this->connection)) {
                $instance->setConnection($this->connection);
            }
            return $instance;
        });
    }
    protected function applyStates(array $definition, array $attributes = [])
    {
        foreach ($this->activeStates as $state) {
            if (! isset($this->states[$this->class][$state])) {
                if ($this->stateHasAfterCallback($state)) {
                    continue;
                }
                throw new InvalidArgumentException("Unable to locate [{$state}] state for [{$this->class}].");
            }
            $definition = array_merge(
                $definition,
                $this->stateAttributes($state, $attributes)
            );
        }
        return $definition;
    }
    protected function stateAttributes($state, array $attributes)
    {
        $stateAttributes = $this->states[$this->class][$state];
        if (! is_callable($stateAttributes)) {
            return $stateAttributes;
        }
        return call_user_func(
            $stateAttributes,
            $this->faker, $attributes
        );
    }
    protected function expandAttributes(array $attributes)
    {
        foreach ($attributes as &$attribute) {
            if (is_callable($attribute) && ! is_string($attribute) && ! is_array($attribute)) {
                $attribute = $attribute($attributes);
            }
            if ($attribute instanceof static) {
                $attribute = $attribute->create()->getKey();
            }
            if ($attribute instanceof Model) {
                $attribute = $attribute->getKey();
            }
        }
        return $attributes;
    }
    public function callAfterMaking($models)
    {
        $this->callAfter($this->afterMaking, $models);
    }
    public function callAfterCreating($models)
    {
        $this->callAfter($this->afterCreating, $models);
    }
    protected function callAfter(array $afterCallbacks, $models)
    {
        $states = array_merge([$this->name], $this->activeStates);
        $models->each(function ($model) use ($states, $afterCallbacks) {
            foreach ($states as $state) {
                $this->callAfterCallbacks($afterCallbacks, $model, $state);
            }
        });
    }
    protected function callAfterCallbacks(array $afterCallbacks, $model, $state)
    {
        if (! isset($afterCallbacks[$this->class][$state])) {
            return;
        }
        foreach ($afterCallbacks[$this->class][$state] as $callback) {
            $callback($model, $this->faker);
        }
    }
    protected function stateHasAfterCallback($state)
    {
        return isset($this->afterMaking[$this->class][$state]) ||
               isset($this->afterCreating[$this->class][$state]);
    }
}
