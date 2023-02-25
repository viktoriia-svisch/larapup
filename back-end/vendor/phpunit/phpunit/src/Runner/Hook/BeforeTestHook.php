<?php declare(strict_types=1);
namespace PHPUnit\Runner;
interface BeforeTestHook extends TestHook
{
    public function executeBeforeTest(string $test): void;
}
