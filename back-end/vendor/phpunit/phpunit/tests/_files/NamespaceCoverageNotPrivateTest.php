<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotPrivateTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
