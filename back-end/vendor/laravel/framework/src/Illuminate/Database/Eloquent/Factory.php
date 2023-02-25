<?php
namespace Illuminate\Database\Eloquent;
use ArrayAccess;
use Faker\Generator as Faker;
use Symfony\Component\Finder\Finder;
class Factory implements ArrayAccess
{
    protected $definitions = [];
    protected $states = [];
    protected $afterMaking = [];
    protected $afterCreating = [];
    protected $faker;
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }
    public static function construct(Faker $faker, $pathToFactories = null)
    {
        $pathToFactories = $pathToFactories ?: database_path('factories');
        return (new static($faker))->load($pathToFactories);
    }
    public function defineAs($class, $name, callable $attributes)
    {
        return $this->define($class, $attributes, $name);
    }
    public function define($class, callable $attributes, $name = 'default')
    {
        $this->definitions[$class][$name] = $attributes;
        return $this;
    }
    public function state($class, $state, $attributes)
    {
        $this->states[$class][$state] = $attributes;
        return $this;
    }
    public function afterMaking($class, callable $callback, $name = 'default')
    {
        $this->afterMaking[$class][$name][] = $callback;
        return $this;
    }
    public function afterMakingState($class, $state, callable $callback)
    {
        return $this->afterMaking($class, $callback, $state);
    }
    public function afterCreating($class, callable $callback, $name = 'default')
    {
        $this->afterCreating[$class][$name][] = $callback;
        return $this;
    }
    public function afterCreatingState($class, $state, callable $callback)
    {
        return $this->afterCreating($class, $callback, $state);
    }
    public function create($class, array $attributes = [])
    {
        return $this->of($class)->create($attributes);
    }
    public function createAs($class, $name, array $attributes = [])
    {
        return $this->of($class, $name)->create($attributes);
    }
    public function make($class, array $attributes = [])
    {
        return $this->of($class)->make($attributes);
    }
    public function makeAs($class, $name, array $attributes = [])
    {
        return $this->of($class, $name)->make($attributes);
    }
    public function rawOf($class, $name, array $attributes = [])
    {
        return $this->raw($class, $attributes, $name);
    }
    public function raw($class, array $attributes = [], $name = 'default')
    {
        return array_merge(
            call_user_func($this->definitions[$class][$name], $this->faker), $attributes
        );
    }
    public function of($class, $name = 'default')
    {
        return new FactoryBuilder(
            $class, $name, $this->definitions, $this->states,
            $this->afterMaking, $this->afterCreating, $this->faker
        );
    }
    public function load($path)
    {
        $factory = $this;
        if (is_dir($path)) {
            foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
                require $file->getRealPath();
            }
        }
        return $factory;
    }
    public function offsetExists($offset)
    {
        return isset($this->definitions[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }
    public function offsetSet($offset, $value)
    {
        return $this->define($offset, $value);
    }
    public function offsetUnset($offset)
    {
        unset($this->definitions[$offset]);
    }
}
