<?php
namespace Foo\DataProviderIssue2922;
use PHPUnit\Framework\TestCase;
class SecondHelloWorldTest extends TestCase
{
    public function testSecond(): void
    {
        $this->assertTrue(true);
    }
}
