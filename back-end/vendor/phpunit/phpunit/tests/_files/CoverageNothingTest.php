<?php
use PHPUnit\Framework\TestCase;
class CoverageNothingTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
