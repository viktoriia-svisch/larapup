<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotProtectedTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
