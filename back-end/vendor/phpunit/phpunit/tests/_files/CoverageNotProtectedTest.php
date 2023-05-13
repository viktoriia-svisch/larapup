<?php
use PHPUnit\Framework\TestCase;
class CoverageNotProtectedTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
