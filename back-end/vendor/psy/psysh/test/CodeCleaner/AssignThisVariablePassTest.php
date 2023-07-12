<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\AssignThisVariablePass;
class AssignThisVariablePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new AssignThisVariablePass());
    }
    public function testProcessStatementFails($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidStatements()
    {
        return [
            ['$this = 3'],
            ['strtolower($this = "this")'],
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
            ['$this'],
            ['$a = $this'],
            ['$a = "this"; $$a = 3'],
            ['$$this = "b"'],
        ];
    }
}
