<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\CallTimePassByReferencePass;
class CallTimePassByReferencePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new CallTimePassByReferencePass());
    }
    public function testProcessStatementFails($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidStatements()
    {
        return [
            ['f(&$arg)'],
            ['$object->method($first, &$arg)'],
            ['$closure($first, &$arg, $last)'],
            ['A::b(&$arg)'],
        ];
    }
    public function testProcessStatementPasses($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function validStatements()
    {
        return [
            ['array(&$var)'],
            ['$a = &$b'],
            ['f(array(&$b))'],
        ];
    }
}
