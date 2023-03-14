<?php
namespace Foo\DataProviderIssue2833;
use PHPUnit\Framework\TestCase;
class SecondTest extends TestCase
{
    public const DUMMY = 'dummy';
    public function testSecond(): void
    {
        $this->assertTrue(true);
    }
}
