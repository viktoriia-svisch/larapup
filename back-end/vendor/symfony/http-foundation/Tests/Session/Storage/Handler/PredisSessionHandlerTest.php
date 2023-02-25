<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use Predis\Client;
class PredisSessionHandlerTest extends AbstractRedisSessionHandlerTestCase
{
    protected function createRedisClient(string $host): Client
    {
        return new Client(['host' => $host]);
    }
}
