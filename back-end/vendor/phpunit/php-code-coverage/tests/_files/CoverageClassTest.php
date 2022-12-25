<?php
use PHPUnit\Framework\TestCase;
class CoverageClassTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
