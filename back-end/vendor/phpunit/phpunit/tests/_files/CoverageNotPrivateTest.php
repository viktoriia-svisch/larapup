<?php
use PHPUnit\Framework\TestCase;
class CoverageNotPrivateTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
