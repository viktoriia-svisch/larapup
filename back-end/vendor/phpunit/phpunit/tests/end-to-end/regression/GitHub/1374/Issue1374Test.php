<?php
use PHPUnit\Framework\TestCase;
class Issue1374Test extends TestCase
{
    protected function setUp(): void
    {
        print __FUNCTION__;
    }
    protected function tearDown(): void
    {
        print __FUNCTION__;
    }
    public function testSomething(): void
    {
        $this->fail('This should not be reached');
    }
}
