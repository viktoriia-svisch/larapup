<?php
use PHPUnit\Framework\TestCase;
class CoverageCoversOverridesCoversNothingTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
