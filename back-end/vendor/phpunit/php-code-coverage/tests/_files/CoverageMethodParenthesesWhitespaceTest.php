<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodParenthesesWhitespaceTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
