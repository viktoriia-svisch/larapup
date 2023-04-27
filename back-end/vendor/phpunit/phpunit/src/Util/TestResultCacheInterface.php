<?php
namespace PHPUnit\Runner;
interface TestResultCacheInterface
{
    public function getState($testName): int;
    public function getTime($testName): float;
    public function load(): void;
    public function persist(): void;
}
