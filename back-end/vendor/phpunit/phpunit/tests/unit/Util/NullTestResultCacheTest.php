<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\NullTestResultCache;
class NullTestResultCacheTest extends TestCase
{
    public function testHasWorkingStubs(): void
    {
        $cache = new NullTestResultCache;
        $cache->load();
        $cache->persist();
        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState('testName'));
        $this->assertSame(0.0, $cache->getTime('testName'));
    }
}
