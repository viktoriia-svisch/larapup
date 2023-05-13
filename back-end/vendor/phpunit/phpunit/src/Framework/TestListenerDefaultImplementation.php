<?php
namespace PHPUnit\Framework;
trait TestListenerDefaultImplementation
{
    public function addError(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function startTestSuite(TestSuite $suite): void
    {
    }
    public function endTestSuite(TestSuite $suite): void
    {
    }
    public function startTest(Test $test): void
    {
    }
    public function endTest(Test $test, float $time): void
    {
    }
}
