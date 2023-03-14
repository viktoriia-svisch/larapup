<?php
namespace Foo\DataProviderIssue2833;
use PHPUnit\Framework\TestCase;
class FirstTest extends TestCase
{
    public function testFirst($x): void
    {
        $this->assertTrue(true);
    }
    public function provide()
    {
        SecondTest::DUMMY;
        return [[true]];
    }
}
