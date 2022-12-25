<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageMethodTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
