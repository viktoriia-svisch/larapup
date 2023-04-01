<?php
use PHPUnit\Framework\TestCase;
class ExceptionInAssertPreConditionsTest extends TestCase
{
    public $setUp                = false;
    public $assertPreConditions  = false;
    public $assertPostConditions = false;
    public $tearDown             = false;
    public $testSomething        = false;
    protected function setUp(): void
    {
        $this->setUp = true;
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
        throw new Exception;
    }
    protected function assertPostConditions(): void
    {
        $this->assertPostConditions = true;
    }
}
