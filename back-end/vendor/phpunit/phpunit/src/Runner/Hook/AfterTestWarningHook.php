<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterTestWarningHook extends TestHook
{
    public function executeAfterTestWarning(string $test, string $message, float $time): void;
}
