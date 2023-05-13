<?php
use PHPUnit\Framework\TestCase;
class TemplateMethodsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        print __METHOD__ . "\n";
    }
    public static function tearDownAfterClass(): void
    {
        print __METHOD__ . "\n";
    }
    protected function setUp(): void
    {
        print __METHOD__ . "\n";
    }
    protected function tearDown(): void
    {
        print __METHOD__ . "\n";
    }
    public function testOne(): void
    {
        print __METHOD__ . "\n";
        $this->assertTrue(true);
    }
    public function testTwo(): void
    {
        print __METHOD__ . "\n";
        $this->assertTrue(false);
    }
    protected function assertPreConditions(): void
    {
        print __METHOD__ . "\n";
    }
    protected function assertPostConditions(): void
    {
        print __METHOD__ . "\n";
    }
    protected function onNotSuccessfulTest(Throwable $t): void
    {
        print __METHOD__ . "\n";
        throw $t;
    }
}
