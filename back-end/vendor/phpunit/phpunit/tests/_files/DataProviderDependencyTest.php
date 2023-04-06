<?php
class DataProviderDependencyTest extends PHPUnit\Framework\TestCase
{
    public function testReference(): void
    {
        $this->markTestSkipped('This test should be skipped.');
        $this->assertTrue(true);
    }
    public function testDependency($param): void
    {
    }
    public function provider()
    {
        $this->markTestSkipped('Any test with this data provider should be skipped.');
        return [];
    }
}
