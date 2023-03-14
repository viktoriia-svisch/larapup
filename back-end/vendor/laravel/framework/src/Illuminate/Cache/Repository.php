<?php
namespace Illuminate\Cache;
use Closure;
use ArrayAccess;
use DateTimeInterface;
use BadMethodCallException;
use Illuminate\Support\Carbon;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Cache\Repository as CacheContract;
class Repository implements CacheContract, ArrayAccess
{
    use InteractsWithTime;
    use Macroable {
        __call as macroCall;
    }
    protected $store;
    protected $events;
    protected $default = 60;
    public function __construct(Store $store)
    {
        $this->store = $store;
    }
    public function has($key)
    {
        return ! is_null($this->get($key));
    }
    public function missing($key)
    {
        return ! $this->has($key);
    }
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->many($key);
        }
        $value = $this->store->get($this->itemKey($key));
        if (is_null($value)) {
            $this->event(new CacheMissed($key));
            $value = value($default);
        } else {
            $this->event(new CacheHit($key, $value));
        }
        return $value;
    }
    public function many(array $keys)
    {
        $values = $this->store->many(collect($keys)->map(function ($value, $key) {
            return is_string($key) ? $key : $value;
        })->values()->all());
        return collect($values)->map(function ($value, $key) use ($keys) {
            return $this->handleManyResult($keys, $key, $value);
        })->all();
    }
    public function getMultiple($keys, $default = null)
    {
        if (is_null($default)) {
            return $this->many($keys);
        }
        foreach ($keys as $key) {
            if (! isset($default[$key])) {
                $default[$key] = null;
            }
        }
        return $this->many($default);
    }
    protected function handleManyResult($keys, $key, $value)
    {
        if (is_null($value)) {
            $this->event(new CacheMissed($key));
            return isset($keys[$key]) ? value($keys[$key]) : null;
        }
        $this->event(new CacheHit($key, $value));
        return $value;
    }
    public function pull($key, $default = null)
    {
        return tap($this->get($key, $default), function () use ($key) {
            $this->forget($key);
        });
    }
    public function put($key, $value, $minutes = null)
    {
        if (is_array($key)) {
            $this->putMany($key, $value);
            return;
        }
        if (! is_null($minutes = $this->getMinutes($minutes))) {
            $this->store->put($this->itemKey($key), $value, $minutes);
            $this->event(new KeyWritten($key, $value, $minutes));
        }
    }
    public function set($key, $value, $ttl = null)
    {
        $this->put($key, $value, $ttl);
    }
    public function putMany(array $values, $minutes)
    {
        if (! is_null($minutes = $this->getMinutes($minutes))) {
            $this->store->putMany($values, $minutes);
            foreach ($values as $key => $value) {
                $this->event(new KeyWritten($key, $value, $minutes));
            }
        }
    }
    public function setMultiple($values, $ttl = null)
    {
        $this->putMany($values, $ttl);
    }
    public function add($key, $value, $minutes)
    {
        if (is_null($minutes = $this->getMinutes($minutes))) {
            return false;
        }
        if (method_exists($this->store, 'add')) {
            return $this->store->add(
                $this->itemKey($key), $value, $minutes
            );
        }
        if (is_null($this->get($key))) {
            $this->put($key, $value, $minutes);
            return true;
        }
        return false;
    }
    public function increment($key, $value = 1)
    {
        return $this->store->increment($key, $value);
    }
    public function decrement($key, $value = 1)
    {
        return $this->store->decrement($key, $value);
    }
    public function forever($key, $value)
    {
        $this->store->forever($this->itemKey($key), $value);
        $this->event(new KeyWritten($key, $value, 0));
    }
    public function remember($key, $minutes, Closure $callback)
    {
        $value = $this->get($key);
        if (! is_null($value)) {
            return $value;
        }
        $this->put($key, $value = $callback(), $minutes);
        return $value;
    }
    public function sear($key, Closure $callback)
    {
        return $this->rememberForever($key, $callback);
    }
    public function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);
        if (! is_null($value)) {
            return $value;
        }
        $this->forever($key, $value = $callback());
        return $value;
    }
    public function forget($key)
    {
        return tap($this->store->forget($this->itemKey($key)), function () use ($key) {
            $this->event(new KeyForgotten($key));
        });
    }
    public function delete($key)
    {
        return $this->forget($key);
    }
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
        return true;
    }
    public function clear()
    {
        return $this->store->flush();
    }
    public function tags($names)
    {
        if (! method_exists($this->store, 'tags')) {
            throw new BadMethodCallException('This cache store does not support tagging.');
        }
        $cache = $this->store->tags(is_array($names) ? $names : func_get_args());
        if (! is_null($this->events)) {
            $cache->setEventDispatcher($this->events);
        }
        return $cache->setDefaultCacheTime($this->default);
    }
    protected function itemKey($key)
    {
        return $key;
    }
    public function getDefaultCacheTime()
    {
        return $this->default;
    }
    public function setDefaultCacheTime($minutes)
    {
        $this->default = $minutes;
        return $this;
    }
    public function getStore()
    {
        return $this->store;
    }
    protected function event($event)
    {
        if (isset($this->events)) {
            $this->events->dispatch($event);
        }
    }
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }
    public function offsetExists($key)
    {
        return $this->has($key);
    }
    public function offsetGet($key)
    {
        return $this->get($key);
    }
    public function offsetSet($key, $value)
    {
        $this->put($key, $value, $this->default);
    }
    public function offsetUnset($key)
    {
        $this->forget($key);
    }
    protected function getMinutes($duration)
    {
        $duration = $this->parseDateInterval($duration);
        if ($duration instanceof DateTimeInterface) {
            $duration = Carbon::now()->diffInRealSeconds($duration, false) / 60;
        }
        return (int) ($duration * 60) > 0 ? $duration : null;
    }
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        return $this->store->$method(...$parameters);
    }
    public function __clone()
    {
        $this->store = clone $this->store;
    }
}
