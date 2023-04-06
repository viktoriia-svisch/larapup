<?php
namespace Illuminate\Redis\Connections;
use Redis;
use Closure;
use Illuminate\Contracts\Redis\Connection as ConnectionContract;
class PhpRedisConnection extends Connection implements ConnectionContract
{
    public function __construct($client)
    {
        $this->client = $client;
    }
    public function get($key)
    {
        $result = $this->command('get', [$key]);
        return $result !== false ? $result : null;
    }
    public function mget(array $keys)
    {
        return array_map(function ($value) {
            return $value !== false ? $value : null;
        }, $this->command('mget', [$keys]));
    }
    public function exists(...$keys)
    {
        $keys = collect($keys)->map(function ($key) {
            return $this->applyPrefix($key);
        })->all();
        return $this->executeRaw(array_merge(['exists'], $keys));
    }
    public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        return $this->command('set', [
            $key,
            $value,
            $expireResolution ? [$flag, $expireResolution => $expireTTL] : null,
        ]);
    }
    public function setnx($key, $value)
    {
        return (int) $this->command('setnx', [$key, $value]);
    }
    public function hmget($key, ...$dictionary)
    {
        if (count($dictionary) === 1) {
            $dictionary = $dictionary[0];
        }
        return array_values($this->command('hmget', [$key, $dictionary]));
    }
    public function hmset($key, ...$dictionary)
    {
        if (count($dictionary) === 1) {
            $dictionary = $dictionary[0];
        } else {
            $input = collect($dictionary);
            $dictionary = $input->nth(2)->combine($input->nth(2, 1))->toArray();
        }
        return $this->command('hmset', [$key, $dictionary]);
    }
    public function hsetnx($hash, $key, $value)
    {
        return (int) $this->command('hsetnx', [$hash, $key, $value]);
    }
    public function lrem($key, $count, $value)
    {
        return $this->command('lrem', [$key, $value, $count]);
    }
    public function blpop(...$arguments)
    {
        $result = $this->command('blpop', $arguments);
        return empty($result) ? null : $result;
    }
    public function brpop(...$arguments)
    {
        $result = $this->command('brpop', $arguments);
        return empty($result) ? null : $result;
    }
    public function spop($key, $count = null)
    {
        return $this->command('spop', [$key]);
    }
    public function zadd($key, ...$dictionary)
    {
        if (is_array(end($dictionary))) {
            foreach (array_pop($dictionary) as $member => $score) {
                $dictionary[] = $score;
                $dictionary[] = $member;
            }
        }
        $key = $this->applyPrefix($key);
        return $this->executeRaw(array_merge(['zadd', $key], $dictionary));
    }
    public function zrangebyscore($key, $min, $max, $options = [])
    {
        if (isset($options['limit'])) {
            $options['limit'] = [
                $options['limit']['offset'],
                $options['limit']['count'],
            ];
        }
        return $this->command('zRangeByScore', [$key, $min, $max, $options]);
    }
    public function zrevrangebyscore($key, $min, $max, $options = [])
    {
        if (isset($options['limit'])) {
            $options['limit'] = [
                $options['limit']['offset'],
                $options['limit']['count'],
            ];
        }
        return $this->command('zRevRangeByScore', [$key, $min, $max, $options]);
    }
    public function zinterstore($output, $keys, $options = [])
    {
        return $this->command('zInter', [$output, $keys,
            $options['weights'] ?? null,
            $options['aggregate'] ?? 'sum',
        ]);
    }
    public function zunionstore($output, $keys, $options = [])
    {
        return $this->command('zUnion', [$output, $keys,
            $options['weights'] ?? null,
            $options['aggregate'] ?? 'sum',
        ]);
    }
    public function pipeline(callable $callback = null)
    {
        $pipeline = $this->client()->pipeline();
        return is_null($callback)
            ? $pipeline
            : tap($pipeline, $callback)->exec();
    }
    public function transaction(callable $callback = null)
    {
        $transaction = $this->client()->multi();
        return is_null($callback)
            ? $transaction
            : tap($transaction, $callback)->exec();
    }
    public function evalsha($script, $numkeys, ...$arguments)
    {
        return $this->command('evalsha', [
            $this->script('load', $script), $arguments, $numkeys,
        ]);
    }
    public function eval($script, $numberOfKeys, ...$arguments)
    {
        return $this->command('eval', [$script, $arguments, $numberOfKeys]);
    }
    public function subscribe($channels, Closure $callback)
    {
        $this->client->subscribe((array) $channels, function ($redis, $channel, $message) use ($callback) {
            $callback($message, $channel);
        });
    }
    public function psubscribe($channels, Closure $callback)
    {
        $this->client->psubscribe((array) $channels, function ($redis, $pattern, $channel, $message) use ($callback) {
            $callback($message, $channel);
        });
    }
    public function createSubscription($channels, Closure $callback, $method = 'subscribe')
    {
    }
    public function executeRaw(array $parameters)
    {
        return $this->command('rawCommand', $parameters);
    }
    public function disconnect()
    {
        $this->client->close();
    }
    private function applyPrefix($key)
    {
        $prefix = (string) $this->client->getOption(Redis::OPT_PREFIX);
        return $prefix.$key;
    }
    public function __call($method, $parameters)
    {
        return parent::__call(strtolower($method), $parameters);
    }
}
