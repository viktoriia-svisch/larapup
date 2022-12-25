<?php
use PHPUnit\Framework\TestCase;
class CoverageMethodParenthesesTest extends TestCase
{
    public function testSomething()
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
