<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
class ExceptionInTestDetectedInTeardown extends TestCase
{
    public $exceptionDetected = false;
    protected function tearDown(): void
    {
        if (BaseTestRunner::STATUS_ERROR == $this->getStatus()) {
            $this->exceptionDetected = true;
        }
    }
    public function testSomething(): void
    {
        throw new Exception;
    }
}
