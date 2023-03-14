<?php
use PHPUnit\Framework\TestCase;
class Issue244Test extends TestCase
{
    public function testWorks(): void
    {
        throw new Issue244Exception;
    }
    public function testFails(): void
    {
        throw new Issue244Exception;
    }
    public function testFailsTooIfExpectationIsANumber(): void
    {
        throw new Issue244Exception;
    }
    public function testFailsTooIfExceptionCodeIsANumber(): void
    {
        throw new Issue244ExceptionIntCode;
    }
}
class Issue244Exception extends Exception
{
    public function __construct()
    {
        $this->code = '123StringCode';
    }
}
class Issue244ExceptionIntCode extends Exception
{
    public function __construct()
    {
        $this->code = 123;
    }
}
