<?php
use PHPUnit\Framework\TestCase;
class CoverageNoneTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
