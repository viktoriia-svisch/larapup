<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\ValidConstantPass;
class ValidConstantPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new ValidConstantPass());
    }
    public function testProcessInvalidConstantReferences($code)
    {
        $this->parseAndTraverse($code);
    }
    public function getInvalidReferences()
    {
        return [
            ['Foo\BAR'],
            ['Psy\Test\CodeCleaner\ValidConstantPassTest::FOO'],
            ['DateTime::BACON'],
        ];
    }
    public function testProcessValidConstantReferences($code)
    {
        $this->parseAndTraverse($code);
        $this->assertTrue(true);
    }
    public function getValidReferences()
    {
        return [
            ['PHP_EOL'],
            ['NotAClass::FOO'],
            ['DateTime::ATOM'],
            ['$a = new DateTime; $a::ATOM'],
            ['DateTime::class'],
            ['$a = new DateTime; $a::class'],
        ];
    }
}
