<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageNotProtectedTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
