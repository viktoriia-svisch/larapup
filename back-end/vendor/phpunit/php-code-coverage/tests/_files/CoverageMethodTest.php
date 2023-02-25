<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
