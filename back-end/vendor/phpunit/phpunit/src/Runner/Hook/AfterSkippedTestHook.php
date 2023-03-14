<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterSkippedTestHook extends TestHook
{
    public function executeAfterSkippedTest(string $test, string $message, float $time): void;
}
