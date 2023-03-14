<?php
use PHPUnit\Framework\TestCase;
class CoveragePrivateTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
