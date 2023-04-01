<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageProtectedTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
