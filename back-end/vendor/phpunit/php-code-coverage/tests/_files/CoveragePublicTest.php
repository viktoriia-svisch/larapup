<?php
use PHPUnit\Framework\TestCase;
class CoveragePublicTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
