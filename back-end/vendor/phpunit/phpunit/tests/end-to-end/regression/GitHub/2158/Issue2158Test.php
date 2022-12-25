<?php
use PHPUnit\Framework\TestCase;
class Issue2158Test extends TestCase
{
    public function testSomething(): void
    {
        include __DIR__ . '/constant.inc';
        $this->assertTrue(true);
    }
    public function testSomethingElse(): void
    {
        $this->assertTrue(\defined('TEST_CONSTANT'));
    }
}
