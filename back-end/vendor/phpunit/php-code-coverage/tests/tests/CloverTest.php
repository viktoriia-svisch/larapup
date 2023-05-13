<?php
namespace SebastianBergmann\CodeCoverage\Report;
use SebastianBergmann\CodeCoverage\TestCase;
class CloverTest extends TestCase
{
    public function testCloverForBankAccountTest()
    {
        $clover = new Clover;
        $this->assertStringMatchesFormatFile(
            TEST_FILES_PATH . 'BankAccount-clover.xml',
            $clover->process($this->getCoverageForBankAccount(), null, 'BankAccount')
        );
    }
    public function testCloverForFileWithIgnoredLines()
    {
        $clover = new Clover;
        $this->assertStringMatchesFormatFile(
            TEST_FILES_PATH . 'ignored-lines-clover.xml',
            $clover->process($this->getCoverageForFileWithIgnoredLines())
        );
    }
    public function testCloverForClassWithAnonymousFunction()
    {
        $clover = new Clover;
        $this->assertStringMatchesFormatFile(
            TEST_FILES_PATH . 'class-with-anonymous-function-clover.xml',
            $clover->process($this->getCoverageForClassWithAnonymousFunction())
        );
    }
}
