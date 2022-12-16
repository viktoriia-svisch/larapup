<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterTestFailureHook extends TestHook
{
    public function executeAfterTestFailure(string $test, string $message, float $time): void;
}
