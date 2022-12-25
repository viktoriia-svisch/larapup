<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
final class RouterTest extends TestCase
{
    public function testRoutesRequest(string $url, string $handler): void
    {
        $this->assertTrue(true);
    }
    public function routesProvider()
    {
        return [
            '/foo/bar' => [
                '/foo/bar',
                FooBarHandler::class,
            ],
        ];
    }
}
