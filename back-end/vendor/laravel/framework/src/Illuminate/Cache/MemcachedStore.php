<?php
namespace Illuminate\Cache;
use Memcached;
use ReflectionMethod;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Cache\LockProvider;
class MemcachedStore extends TaggableStore implements LockProvider, Store
{
    use InteractsWithTime;
    const REALTIME_MAXDELTA_IN_MINUTES = 43200;
    protected $memcached;
    protected $prefix;
    protected $onVersionThree;
    public function __construct($memcached, $prefix = '')
    {
        $this->setPrefix($prefix);
        $this->memcached = $memcached;
        $this->onVersionThree = (new ReflectionMethod('Memcached', 'getMulti'))
                            ->getNumberOfParameters() == 2;
    }
    public function get($key)
    {
        $value = $this->memcached->get($this->prefix.$key);
        if ($this->memcached->getResultCode() == 0) {
            return $value;
        }
    }
    public function many(array $keys)
    {
        $prefixedKeys = array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys);
        if ($this->onVersionThree) {
            $values = $this->memcached->getMulti($prefixedKeys, Memcached::GET_PRESERVE_ORDER);
        } else {
            $null = null;
            $values = $this->memcached->getMulti($prefixedKeys, $null, Memcached::GET_PRESERVE_ORDER);
        }
        if ($this->memcached->getResultCode() != 0) {
            return array_fill_keys($keys, null);
        }
        return array_combine($keys, $values);
    }
    public function put($key, $value, $minutes)
    {
        $this->memcached->set(
            $this->prefix.$key, $value, $this->calculateExpiration($minutes)
        );
    }
    public function putMany(array $values, $minutes)
    {
        $prefixedValues = [];
        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefix.$key] = $value;
        }
        $this->memcached->setMulti(
            $prefixedValues, $this->calculateExpiration($minutes)
        );
    }
    public function add($key, $value, $minutes)
    {
        return $this->memcached->add(
            $this->prefix.$key, $value, $this->calculateExpiration($minutes)
        );
    }
    public function increment($key, $value = 1)
    {
        return $this->memcached->increment($this->prefix.$key, $value);
    }
    public function decrement($key, $value = 1)
    {
        return $this->memcached->decrement($this->prefix.$key, $value);
    }
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }
    public function lock($name, $seconds = 0)
    {
        return new MemcachedLock($this->memcached, $this->prefix.$name, $seconds);
    }
    public function forget($key)
    {
        return $this->memcached->delete($this->prefix.$key);
    }
    public function flush()
    {
        return $this->memcached->flush();
    }
    protected function calculateExpiration($minutes)
    {
        return $this->toTimestamp($minutes);
    }
    protected function toTimestamp($minutes)
    {
        return $minutes > 0 ? $this->availableAt($minutes * 60) : 0;
    }
    public function getMemcached()
    {
        return $this->memcached;
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }
}
