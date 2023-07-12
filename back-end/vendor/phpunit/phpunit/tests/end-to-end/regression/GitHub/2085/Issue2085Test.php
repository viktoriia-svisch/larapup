<?php
use PHPUnit\Framework\TestCase;
class Issue2085Test extends TestCase
{
    public function testShouldAbortSlowTestByEnforcingTimeLimit(): void
    {
        $this->assertTrue(true);
        \sleep(1.2);
        $this->assertTrue(true);
    }
}
