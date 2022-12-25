<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterTestHook extends Hook
{
    public function executeAfterTest(string $test, float $time): void;
}
