<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageCoversClassPublicTest extends TestCase
{
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
