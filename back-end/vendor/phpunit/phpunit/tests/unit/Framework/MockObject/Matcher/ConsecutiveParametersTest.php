<?php
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
class ConsecutiveParametersTest extends TestCase
{
    public function testIntegration(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();
        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar'],
                 [21, 42]
             );
        $this->assertNull($mock->foo('bar'));
        $this->assertNull($mock->foo(21, 42));
    }
    public function testIntegrationWithLessAssertionsThanMethodCalls(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();
        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar']
             );
        $this->assertNull($mock->foo('bar'));
        $this->assertNull($mock->foo(21, 42));
    }
    public function testIntegrationExpectingException(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();
        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar'],
                 [21, 42]
             );
        $mock->foo('bar');
        $this->expectException(ExpectationFailedException::class);
        $mock->foo('invalid');
    }
}
