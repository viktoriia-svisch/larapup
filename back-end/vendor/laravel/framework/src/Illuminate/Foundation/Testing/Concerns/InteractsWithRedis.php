<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Exception;
use Illuminate\Redis\RedisManager;
use Illuminate\Foundation\Application;
trait InteractsWithRedis
{
    private static $connectionFailedOnceWithDefaultsSkip = false;
    private $redis;
    public function setUpRedis()
    {
        $app = $this->app ?? new Application;
        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = getenv('REDIS_PORT') ?: 6379;
        if (static::$connectionFailedOnceWithDefaultsSkip) {
            $this->markTestSkipped('Trying default host/port failed, please set environment variable REDIS_HOST & REDIS_PORT to enable '.__CLASS__);
            return;
        }
        foreach ($this->redisDriverProvider() as $driver) {
            $this->redis[$driver[0]] = new RedisManager($app, $driver[0], [
                'cluster' => false,
                'default' => [
                    'host' => $host,
                    'port' => $port,
                    'database' => 5,
                    'timeout' => 0.5,
                ],
            ]);
        }
        try {
            $this->redis['predis']->connection()->flushdb();
        } catch (Exception $e) {
            if ($host === '127.0.0.1' && $port === 6379 && getenv('REDIS_HOST') === false) {
                static::$connectionFailedOnceWithDefaultsSkip = true;
                $this->markTestSkipped('Trying default host/port failed, please set environment variable REDIS_HOST & REDIS_PORT to enable '.__CLASS__);
            }
        }
    }
    public function tearDownRedis()
    {
        $this->redis['predis']->connection()->flushdb();
        foreach ($this->redisDriverProvider() as $driver) {
            $this->redis[$driver[0]]->connection()->disconnect();
        }
    }
    public function redisDriverProvider()
    {
        $providers = [
            ['predis'],
        ];
        if (extension_loaded('redis')) {
            $providers[] = ['phpredis'];
        }
        return $providers;
    }
    public function ifRedisAvailable($callback)
    {
        $this->setUpRedis();
        $callback();
        $this->tearDownRedis();
    }
}
