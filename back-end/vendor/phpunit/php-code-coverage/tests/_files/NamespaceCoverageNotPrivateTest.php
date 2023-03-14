<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotPrivateTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
