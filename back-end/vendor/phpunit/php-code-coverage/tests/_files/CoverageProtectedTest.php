<?php
use PHPUnit\Framework\TestCase;
class CoverageProtectedTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
