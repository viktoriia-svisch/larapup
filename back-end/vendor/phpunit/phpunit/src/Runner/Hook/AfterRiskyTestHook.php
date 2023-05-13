<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterRiskyTestHook extends TestHook
{
    public function executeAfterRiskyTest(string $test, string $message, float $time): void;
}
