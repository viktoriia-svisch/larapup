<?php
use PHPUnit\Framework\TestCase;
class CoverageNotPrivateTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
