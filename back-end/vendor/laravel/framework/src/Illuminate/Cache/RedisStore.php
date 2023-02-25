<?php
namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Redis\Factory as Redis;
class RedisStore extends TaggableStore implements Store
{
    protected $redis;
    protected $prefix;
    protected $connection;
    public function __construct(Redis $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
        $this->setConnection($connection);
    }
    public function get($key)
    {
        $value = $this->connection()->get($this->prefix.$key);
        return ! is_null($value) ? $this->unserialize($value) : null;
    }
    public function many(array $keys)
    {
        $results = [];
        $values = $this->connection()->mget(array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys));
        foreach ($values as $index => $value) {
            $results[$keys[$index]] = ! is_null($value) ? $this->unserialize($value) : null;
        }
        return $results;
    }
    public function put($key, $value, $minutes)
    {
        $this->connection()->setex(
            $this->prefix.$key, (int) max(1, $minutes * 60), $this->serialize($value)
        );
    }
    public function putMany(array $values, $minutes)
    {
        $this->connection()->multi();
        foreach ($values as $key => $value) {
            $this->put($key, $value, $minutes);
        }
        $this->connection()->exec();
    }
    public function add($key, $value, $minutes)
    {
        $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('setex',KEYS[1],ARGV[2],ARGV[1])";
        return (bool) $this->connection()->eval(
            $lua, 1, $this->prefix.$key, $this->serialize($value), (int) max(1, $minutes * 60)
        );
    }
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrby($this->prefix.$key, $value);
    }
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrby($this->prefix.$key, $value);
    }
    public function forever($key, $value)
    {
        $this->connection()->set($this->prefix.$key, $this->serialize($value));
    }
    public function lock($name, $seconds = 0)
    {
        return new RedisLock($this->connection(), $this->prefix.$name, $seconds);
    }
    public function forget($key)
    {
        return (bool) $this->connection()->del($this->prefix.$key);
    }
    public function flush()
    {
        $this->connection()->flushdb();
        return true;
    }
    public function tags($names)
    {
        return new RedisTaggedCache(
            $this, new TagSet($this, is_array($names) ? $names : func_get_args())
        );
    }
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }
    public function getRedis()
    {
        return $this->redis;
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function setPrefix($prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }
    protected function serialize($value)
    {
        return is_numeric($value) ? $value : serialize($value);
    }
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}
