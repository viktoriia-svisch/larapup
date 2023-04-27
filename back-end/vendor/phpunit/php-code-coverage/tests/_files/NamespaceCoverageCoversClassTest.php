<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageCoversClassTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
