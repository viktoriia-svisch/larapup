<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface BeforeFirstTestHook extends Hook
{
    public function executeBeforeFirstTest(): void;
}
