<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotPublicTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
