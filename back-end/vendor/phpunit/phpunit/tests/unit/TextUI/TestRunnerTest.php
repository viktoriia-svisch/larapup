<?php
namespace PHPUnit\TextUI;
use PHPUnit\Framework\TestCase;
class TestRunnerTest extends TestCase
{
    public function testTestIsRunnable(): void
    {
        $runner = new TestRunner;
        $runner->setPrinter($this->getResultPrinterMock());
        $runner->doRun(new \Success, ['filter' => 'foo'], false);
    }
    public function testSuiteIsRunnable(): void
    {
        $runner = new TestRunner;
        $runner->setPrinter($this->getResultPrinterMock());
        $runner->doRun($this->getSuiteMock(), ['filter' => 'foo'], false);
    }
    private function getResultPrinterMock()
    {
        return $this->createMock(\PHPUnit\TextUI\ResultPrinter::class);
    }
    private function getSuiteMock()
    {
        $suite = $this->createMock(\PHPUnit\Framework\TestSuite::class);
        $suite->expects($this->once())->method('injectFilter');
        $suite->expects($this->once())->method('run');
        return $suite;
    }
}
