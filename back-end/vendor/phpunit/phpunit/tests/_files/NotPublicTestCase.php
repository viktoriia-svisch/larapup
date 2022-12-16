<?php
use PHPUnit\Framework\TestCase;
class NotPublicTestCase extends TestCase
{
    public function testPublic(): void
    {
    }
    protected function testNotPublic(): void
    {
    }
}
