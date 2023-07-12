<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageClassTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
