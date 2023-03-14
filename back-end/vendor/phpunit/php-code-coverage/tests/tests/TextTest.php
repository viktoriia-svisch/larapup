<?php
namespace SebastianBergmann\CodeCoverage\Report;
use SebastianBergmann\CodeCoverage\TestCase;
class TextTest extends TestCase
{
    public function testTextForBankAccountTest()
    {
        $text = new Text(50, 90, false, false);
        $this->assertStringMatchesFormatFile(
            TEST_FILES_PATH . 'BankAccount-text.txt',
            str_replace(PHP_EOL, "\n", $text->process($this->getCoverageForBankAccount()))
        );
    }
    public function testTextForFileWithIgnoredLines()
    {
        $text = new Text(50, 90, false, false);
        $this->assertStringMatchesFormatFile(
            TEST_FILES_PATH . 'ignored-lines-text.txt',
            str_replace(PHP_EOL, "\n", $text->process($this->getCoverageForFileWithIgnoredLines()))
        );
    }
    public function testTextForClassWithAnonymousFunction()
    {
        $text = new Text(50, 90, false, false);
        $this->assertStringMatchesFormatFile(
            TEST_FILES_PATH . 'class-with-anonymous-function-text.txt',
            str_replace(PHP_EOL, "\n", $text->process($this->getCoverageForClassWithAnonymousFunction()))
        );
    }
}
