<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoveragePrivateTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
