<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterIncompleteTestHook extends TestHook
{
    public function executeAfterIncompleteTest(string $test, string $message, float $time): void;
}
