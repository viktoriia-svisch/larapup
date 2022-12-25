<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\FunctionContextPass;
class FunctionContextPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new FunctionContextPass());
    }
    public function testProcessStatementPasses($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function validStatements()
    {
        return [
            ['function foo() { yield; }'],
            ['if (function(){ yield; })'],
        ];
    }
    public function testInvalidYield($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidYieldStatements()
    {
        return [
            ['yield'],
            ['if (yield)'],
        ];
    }
}
