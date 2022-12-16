<?php
namespace Foo\DataProviderIssue2922;
use PHPUnit\Framework\TestCase;
class FirstTest extends TestCase
{
    public function testFirst($x): void
    {
        $this->assertTrue(true);
    }
    public function provide(): void
    {
        throw new \Exception;
    }
}
