<?php
use PHPUnit\Framework\TestCase;
class Issue1021Test extends TestCase
{
    public function testSomething($data): void
    {
        $this->assertTrue($data);
    }
    public function testSomethingElse(): void
    {
        $this->assertTrue(true);
    }
    public function provider()
    {
        return [[true]];
    }
}
