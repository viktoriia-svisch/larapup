<?php
use PHPUnit\Framework\TestCase;
class CoverageProtectedTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
