<?php
use PHPUnit\Framework\TestCase;
class ExceptionInSetUpTest extends TestCase
{
    public $setUp                = false;
    public $assertPreConditions  = false;
    public $assertPostConditions = false;
    public $tearDown             = false;
    public $testSomething        = false;
    protected function setUp(): void
    {
        $this->setUp = true;
        throw new Exception;
    }
    protected function tearDown(): void
    {
        $this->tearDown = true;
    }
    public function testSomething(): void
    {
        $this->testSomething = true;
    }
    protected function assertPreConditions(): void
    {
        $this->assertPreConditions = true;
    }
    protected function assertPostConditions(): void
    {
        $this->assertPostConditions = true;
    }
}
