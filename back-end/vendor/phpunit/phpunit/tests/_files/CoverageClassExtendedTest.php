<?php
use PHPUnit\Framework\TestCase;
class CoverageClassExtendedTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
