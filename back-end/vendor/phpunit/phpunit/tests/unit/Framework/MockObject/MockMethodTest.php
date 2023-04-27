<?php
declare(strict_types=1);
namespace PHPUnit\Framework\MockObject;
use PHPUnit\Framework\TestCase;
class MockMethodTest extends TestCase
{
    public function testGetNameReturnsMethodName()
    {
        $method = new MockMethod(
            'ClassName',
            'methodName',
            false,
            '',
            '',
            '',
            '',
            '',
            false,
            false,
            null,
            false
        );
        $this->assertEquals('methodName', $method->getName());
    }
    public function testFailWhenReturnTypeIsParentButThereIsNoParentClass()
    {
        $method = new MockMethod(
            \stdClass::class,
            'methodName',
            false,
            '',
            '',
            '',
            'parent',
            '',
            false,
            false,
            null,
            false
        );
        $this->expectException(\RuntimeException::class);
        $method->generateCode();
    }
}
