<?php
namespace Foo\DataProviderIssue2859;
use PHPUnit\Framework\TestCase;
class TestWithDataProviderTest extends TestCase
{
    public function testFirst($x): void
    {
        $this->assertTrue(true);
    }
    public function provide()
    {
        return [[true]];
    }
}
