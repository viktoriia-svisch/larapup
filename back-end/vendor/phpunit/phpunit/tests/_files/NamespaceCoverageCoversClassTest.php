<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageCoversClassTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
