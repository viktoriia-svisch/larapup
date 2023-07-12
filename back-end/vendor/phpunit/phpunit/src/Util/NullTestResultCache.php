<?php
namespace PHPUnit\Runner;
class NullTestResultCache implements TestResultCacheInterface
{
    public function getState($testName): int
    {
        return BaseTestRunner::STATUS_UNKNOWN;
    }
    public function getTime($testName): float
    {
        return 0;
    }
    public function load(): void
    {
    }
    public function persist(): void
    {
    }
}
