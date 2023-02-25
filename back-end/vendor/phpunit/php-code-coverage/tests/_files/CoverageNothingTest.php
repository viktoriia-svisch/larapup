<?php
use PHPUnit\Framework\TestCase;
class CoverageNothingTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
