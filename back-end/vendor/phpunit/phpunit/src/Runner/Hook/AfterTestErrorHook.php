<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterTestErrorHook extends TestHook
{
    public function executeAfterTestError(string $test, string $message, float $time): void;
}
