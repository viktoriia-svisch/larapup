<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use Predis\Client;
class PredisClusterSessionHandlerTest extends AbstractRedisSessionHandlerTestCase
{
    protected function createRedisClient(string $host): Client
    {
        return new Client([['host' => $host]]);
    }
}
