<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
