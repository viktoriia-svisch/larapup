<?php
use PHPUnit\Framework\TestCase;
class NamespaceCoverageCoversClassPublicTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}