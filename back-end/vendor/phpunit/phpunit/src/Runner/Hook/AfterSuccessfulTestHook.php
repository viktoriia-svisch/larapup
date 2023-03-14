<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterSuccessfulTestHook extends TestHook
{
    public function executeAfterSuccessfulTest(string $test, float $time): void;
}
