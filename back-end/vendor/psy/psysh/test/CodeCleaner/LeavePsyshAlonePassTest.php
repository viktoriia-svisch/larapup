<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\LeavePsyshAlonePass;
class LeavePsyshAlonePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new LeavePsyshAlonePass());
    }
    public function testPassesInlineHtmlThroughJustFine()
    {
        $inline = $this->parse('not php at all!', '');
        $this->traverse($inline);
        $this->assertTrue(true);
    }
    public function testProcessStatementPasses($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function validStatements()
    {
        return [
            ['array_merge()'],
            ['__psysh__()'],
            ['$this'],
            ['$psysh'],
            ['$__psysh'],
            ['$banana'],
        ];
    }
    public function testProcessStatementFails($code)
    {
        $this->parseAndTraverse($code);
    }
    public function invalidStatements()
    {
        return [
            ['$__psysh__'],
            ['var_dump($__psysh__)'],
            ['$__psysh__ = "your mom"'],
            ['$__psysh__->fakeFunctionCall()'],
        ];
    }
}
