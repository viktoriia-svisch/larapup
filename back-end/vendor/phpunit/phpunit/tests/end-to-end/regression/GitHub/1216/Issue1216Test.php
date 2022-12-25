<?php
use PHPUnit\Framework\TestCase;
class Issue1216Test extends TestCase
{
    public function testConfigAvailableInBootstrap(): void
    {
        $this->assertTrue($_ENV['configAvailableInBootstrap']);
    }
}
