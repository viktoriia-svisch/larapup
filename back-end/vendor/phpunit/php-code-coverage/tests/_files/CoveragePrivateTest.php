<?php
use PHPUnit\Framework\TestCase;
class CoveragePrivateTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
