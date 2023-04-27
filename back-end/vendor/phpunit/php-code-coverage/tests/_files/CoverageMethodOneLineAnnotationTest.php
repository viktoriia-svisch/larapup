<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodOneLineAnnotationTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
