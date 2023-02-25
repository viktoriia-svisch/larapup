<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
class RedisClusterSessionHandlerTest extends AbstractRedisSessionHandlerTestCase
{
    public static function setupBeforeClass()
    {
        if (!class_exists('RedisCluster')) {
            self::markTestSkipped('The RedisCluster class is required.');
        }
        if (!$hosts = getenv('REDIS_CLUSTER_HOSTS')) {
            self::markTestSkipped('REDIS_CLUSTER_HOSTS env var is not defined.');
        }
    }
    protected function createRedisClient(string $host): \RedisCluster
    {
        return new \RedisCluster(null, explode(' ', getenv('REDIS_CLUSTER_HOSTS')));
    }
}
