<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodParenthesesWhitespaceTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
