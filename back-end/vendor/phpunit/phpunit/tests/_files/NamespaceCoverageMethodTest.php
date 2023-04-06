<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageMethodTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
