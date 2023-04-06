<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\ExitPass;
class ExitPassTest extends CodeCleanerTestCase
{
    private $expectedExceptionString = '\\Psy\\Exception\\BreakException::exitShell()';
    public function setUp()
    {
        $this->setPass(new ExitPass());
    }
    public function testExitStatement($from, $to)
    {
        $this->assertProcessesAs($from, $to);
    }
    public function dataProviderExitStatement()
    {
        return [
            ['exit;', "{$this->expectedExceptionString};"],
            ['exit();', "{$this->expectedExceptionString};"],
            ['die;', "{$this->expectedExceptionString};"],
            ['exit(die(die));', "{$this->expectedExceptionString};"],
            ['if (true) { exit; }', "if (true) {\n    {$this->expectedExceptionString};\n}"],
            ['if (false) { exit; }', "if (false) {\n    {$this->expectedExceptionString};\n}"],
            ['1 and exit();', "1 and {$this->expectedExceptionString};"],
            ['foo() or die', "foo() or {$this->expectedExceptionString};"],
            ['exit and 1;', "{$this->expectedExceptionString} and 1;"],
            ['if (exit) { echo $wat; }', "if ({$this->expectedExceptionString}) {\n    echo \$wat;\n}"],
            ['exit or die;', "{$this->expectedExceptionString} or {$this->expectedExceptionString};"],
            ['switch (die) { }', "switch ({$this->expectedExceptionString}) {\n}"],
            ['for ($i = 1; $i < 10; die) {}', "for (\$i = 1; \$i < 10; {$this->expectedExceptionString}) {\n}"],
        ];
    }
}
