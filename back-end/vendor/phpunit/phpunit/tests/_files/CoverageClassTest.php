<?php
use PHPUnit\Framework\TestCase;
class CoverageClassTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
