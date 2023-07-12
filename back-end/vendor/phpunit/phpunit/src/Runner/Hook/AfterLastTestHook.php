<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface AfterLastTestHook extends Hook
{
    public function executeAfterLastTest(): void;
}
