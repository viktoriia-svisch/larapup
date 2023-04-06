<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageClassExtendedTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
