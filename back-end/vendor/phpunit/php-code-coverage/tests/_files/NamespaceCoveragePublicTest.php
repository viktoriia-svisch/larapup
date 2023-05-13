<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoveragePublicTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
