<?php
use PHPUnit\Framework\TestCase;
class CoverageNotPublicTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
