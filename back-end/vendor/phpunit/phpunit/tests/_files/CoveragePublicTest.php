<?php
use PHPUnit\Framework\TestCase;
class CoveragePublicTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
