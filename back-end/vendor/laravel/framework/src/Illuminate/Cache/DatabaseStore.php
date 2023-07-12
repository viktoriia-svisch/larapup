<?php
namespace Illuminate\Cache;
use Closure;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\ConnectionInterface;
class DatabaseStore implements Store
{
    use InteractsWithTime, RetrievesMultipleKeys;
    protected $connection;
    protected $table;
    protected $prefix;
    public function __construct(ConnectionInterface $connection, $table, $prefix = '')
    {
        $this->table = $table;
        $this->prefix = $prefix;
        $this->connection = $connection;
    }
    public function get($key)
    {
        $prefixed = $this->prefix.$key;
        $cache = $this->table()->where('key', '=', $prefixed)->first();
        if (is_null($cache)) {
            return;
        }
        $cache = is_array($cache) ? (object) $cache : $cache;
        if ($this->currentTime() >= $cache->expiration) {
            $this->forget($key);
            return;
        }
        return $this->unserialize($cache->value);
    }
    public function put($key, $value, $minutes)
    {
        $key = $this->prefix.$key;
        $value = $this->serialize($value);
        $expiration = $this->getTime() + (int) ($minutes * 60);
        try {
            $this->table()->insert(compact('key', 'value', 'expiration'));
        } catch (Exception $e) {
            $this->table()->where('key', $key)->update(compact('value', 'expiration'));
        }
    }
    public function increment($key, $value = 1)
    {
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current + $value;
        });
    }
    public function decrement($key, $value = 1)
    {
        return $this->incrementOrDecrement($key, $value, function ($current, $value) {
            return $current - $value;
        });
    }
    protected function incrementOrDecrement($key, $value, Closure $callback)
    {
        return $this->connection->transaction(function () use ($key, $value, $callback) {
            $prefixed = $this->prefix.$key;
            $cache = $this->table()->where('key', $prefixed)
                        ->lockForUpdate()->first();
            if (is_null($cache)) {
                return false;
            }
            $cache = is_array($cache) ? (object) $cache : $cache;
            $current = $this->unserialize($cache->value);
            $new = $callback((int) $current, $value);
            if (! is_numeric($current)) {
                return false;
            }
            $this->table()->where('key', $prefixed)->update([
                'value' => $this->serialize($new),
            ]);
            return $new;
        });
    }
    protected function getTime()
    {
        return $this->currentTime();
    }
    public function forever($key, $value)
    {
        $this->put($key, $value, 5256000);
    }
    public function forget($key)
    {
        $this->table()->where('key', '=', $this->prefix.$key)->delete();
        return true;
    }
    public function flush()
    {
        $this->table()->delete();
        return true;
    }
    protected function table()
    {
        return $this->connection->table($this->table);
    }
    public function getConnection()
    {
        return $this->connection;
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
    protected function serialize($value)
    {
        $result = serialize($value);
        if ($this->connection instanceof PostgresConnection && Str::contains($result, "\0")) {
            $result = base64_encode($result);
        }
        return $result;
    }
    protected function unserialize($value)
    {
        if ($this->connection instanceof PostgresConnection && ! Str::contains($value, [':', ';'])) {
            $value = base64_decode($value);
        }
        return unserialize($value);
    }
}
