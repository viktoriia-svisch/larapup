<?php
use PHPUnit\Framework\TestCase;
class CoverageNotProtectedTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
