<?php
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
class DoubleTestCase implements Test
{
    protected $testCase;
    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
    public function count()
    {
        return 2;
    }
    public function run(TestResult $result = null): TestResult
    {
        $result->startTest($this);
        $this->testCase->runBare();
        $this->testCase->runBare();
        $result->endTest($this, 0);
        return $result;
    }
}
