<?php
use PHPUnit\Framework\TestCase;
class AssertionExampleTest extends TestCase
{
    public function testOne(): void
    {
        $e = new AssertionExample;
        $e->doSomething();
    }
}
