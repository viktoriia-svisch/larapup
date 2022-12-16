<?php
class CoverageTwoDefaultClassAnnotations
{
    public function testSomething(): void
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
