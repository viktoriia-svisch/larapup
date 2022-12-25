<?php
namespace Psy\Test;
use Psy\CodeCleaner;
class CodeCleanerTest extends \PHPUnit\Framework\TestCase
{
    public function testAutomaticSemicolons(array $lines, $requireSemicolons, $expected)
    {
        $cc = new CodeCleaner();
        $this->assertSame($expected, $cc->clean($lines, $requireSemicolons));
    }
    public function semicolonCodeProvider()
    {
        return [
            [['true'],  false, 'return true;'],
            [['true;'], false, 'return true;'],
            [['true;'], true,  'return true;'],
            [['true'],  true,  false],
            [['echo "foo";', 'true'], true,  false],
            [['echo "foo";', 'true'], false, "echo \"foo\";\nreturn true;"],
        ];
    }
    public function testUnclosedStatements(array $lines, $isUnclosed)
    {
        $cc  = new CodeCleaner();
        $res = $cc->clean($lines);
        if ($isUnclosed) {
            $this->assertFalse($res);
        } else {
            $this->assertNotFalse($res);
        }
    }
    public function unclosedStatementsProvider()
    {
        return [
            [['echo "'],   true],
            [['echo \''],  true],
            [['if (1) {'], true],
            [['echo "foo",'], true],
            [['echo ""'],   false],
            [["echo ''"],   false],
            [['if (1) {}'], false],
            [['
            [['function foo() { 
    public function testMoreUnclosedStatements(array $lines)
    {
        if (\defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM not supported.');
        }
        $cc  = new CodeCleaner();
        $res = $cc->clean($lines);
        $this->assertFalse($res);
    }
    public function moreUnclosedStatementsProvider()
    {
        return [
            [["\$content = <<<EOS\n"]],
            [["\$content = <<<'EOS'\n"]],
            [['
    public function testInvalidStatementsThrowParseErrors($code)
    {
        $cc = new CodeCleaner();
        $cc->clean([$code]);
    }
    public function invalidStatementsProvider()
    {
        return [
            ['function "what'],
            ["function 'what"],
            ['echo }'],
            ['echo {'],
            ['if (1) }'],
            ['echo """'],
            ["echo '''"],
            ['$foo "bar'],
            ['$foo \'bar'],
        ];
    }
}
