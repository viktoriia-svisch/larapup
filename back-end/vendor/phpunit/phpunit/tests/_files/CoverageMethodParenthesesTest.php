<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodParenthesesTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
