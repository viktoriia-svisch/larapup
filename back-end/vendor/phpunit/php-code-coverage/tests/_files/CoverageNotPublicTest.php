<?php
use PHPUnit\Framework\TestCase;
class CoverageNotPublicTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
