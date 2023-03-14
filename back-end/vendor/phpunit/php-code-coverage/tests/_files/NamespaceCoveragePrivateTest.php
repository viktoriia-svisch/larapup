<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoveragePrivateTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
