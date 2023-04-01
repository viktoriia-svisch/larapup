<?php
use PHPUnit\Framework\TestCase;
class CoverageClassExtendedTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
