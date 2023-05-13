<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotPublicTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
