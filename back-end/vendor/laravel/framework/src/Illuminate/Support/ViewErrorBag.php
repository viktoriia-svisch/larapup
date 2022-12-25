<?php
namespace Illuminate\Support;
use Countable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
class ViewErrorBag implements Countable
{
    protected $bags = [];
    public function hasBag($key = 'default')
    {
        return isset($this->bags[$key]);
    }
    public function getBag($key)
    {
        return Arr::get($this->bags, $key) ?: new MessageBag;
    }
    public function getBags()
    {
        return $this->bags;
    }
    public function put($key, MessageBagContract $bag)
    {
        $this->bags[$key] = $bag;
        return $this;
    }
    public function any()
    {
        return $this->count() > 0;
    }
    public function count()
    {
        return $this->getBag('default')->count();
    }
    public function __call($method, $parameters)
    {
        return $this->getBag('default')->$method(...$parameters);
    }
    public function __get($key)
    {
        return $this->getBag($key);
    }
    public function __set($key, $value)
    {
        $this->put($key, $value);
    }
    public function __toString()
    {
        return (string) $this->getBag('default');
    }
}
