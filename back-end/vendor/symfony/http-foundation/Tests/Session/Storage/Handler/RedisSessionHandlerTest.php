<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
class RedisSessionHandlerTest extends AbstractRedisSessionHandlerTestCase
{
    protected function createRedisClient(string $host): \Redis
    {
        $client = new \Redis();
        $client->connect($host);
        return $client;
    }
}
