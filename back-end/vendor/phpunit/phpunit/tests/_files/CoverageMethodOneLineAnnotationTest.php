<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodOneLineAnnotationTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
