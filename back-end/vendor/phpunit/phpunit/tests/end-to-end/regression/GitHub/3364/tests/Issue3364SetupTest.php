<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class Issue3364SetupTest extends TestCase
{
    public function setUp(): void
    {
        throw new \RuntimeException('throw exception in setUp');
    }
    public function testOneWithSetupException(): void
    {
        $this->fail();
    }
    public function testTwoWithSetupException(): void
    {
        $this->fail();
    }
}
