<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoveragePublicTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
