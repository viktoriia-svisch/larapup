<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
class RedisArraySessionHandlerTest extends AbstractRedisSessionHandlerTestCase
{
    protected function createRedisClient(string $host): \RedisArray
    {
        return new \RedisArray([$host]);
    }
}
