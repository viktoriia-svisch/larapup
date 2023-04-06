<?php
declare(strict_types=1); 
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingParameterAndReturnTypesTest extends MockeryTestCase
{
    public function testMockingStringReturnType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnString");
        $this->assertSame("", $mock->returnString());
    }
    public function testMockingIntegerReturnType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnInteger");
        $this->assertSame(0, $mock->returnInteger());
    }
    public function testMockingFloatReturnType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnFloat");
        $this->assertSame(0.0, $mock->returnFloat());
    }
    public function testMockingBooleanReturnType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnBoolean");
        $this->assertFalse($mock->returnBoolean());
    }
    public function testMockingArrayReturnType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnArray");
        $this->assertSame([], $mock->returnArray());
    }
    public function testMockingGeneratorReturnTyps()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnGenerator");
        $this->assertInstanceOf("\Generator", $mock->returnGenerator());
    }
    public function testMockingCallableReturnType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("returnCallable");
        $this->assertTrue(is_callable($mock->returnCallable()));
    }
    public function testMockingClassReturnTypes()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("withClassReturnType");
        $this->assertInstanceOf("test\Mockery\TestWithParameterAndReturnType", $mock->withClassReturnType());
    }
    public function testMockingParameterTypes()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldReceive("withScalarParameters");
        $mock->withScalarParameters(1, 1.0, true, 'string');
    }
    public function testIgnoringMissingReturnsType()
    {
        $mock = mock("test\Mockery\TestWithParameterAndReturnType");
        $mock->shouldIgnoreMissing();
        $this->assertSame('', $mock->returnString());
        $this->assertSame(0, $mock->returnInteger());
        $this->assertSame(0.0, $mock->returnFloat());
        $this->assertFalse( $mock->returnBoolean());
        $this->assertSame([], $mock->returnArray());
        $this->assertTrue(is_callable($mock->returnCallable()));
        $this->assertInstanceOf("\Generator", $mock->returnGenerator());
        $this->assertInstanceOf("test\Mockery\TestWithParameterAndReturnType", $mock->withClassReturnType());
    }
    public function testAutoStubbingSelf()
    {
        $spy = \Mockery::spy("test\Mockery\TestWithParameterAndReturnType");
        $this->assertInstanceOf("test\Mockery\TestWithParameterAndReturnType", $spy->returnSelf());
    }
    public function testItShouldMockClassWithHintedParamsInMagicMethod()
    {
        $this->assertNotNull(
            \Mockery::mock('test\Mockery\MagicParams')
        );
    }
    public function testItShouldMockClassWithHintedReturnInMagicMethod()
    {
        $this->assertNotNull(
            \Mockery::mock('test\Mockery\MagicReturns')
        );
    }
}
class MagicParams
{
    public function __isset(string $property)
    {
        return false;
    }
}
class MagicReturns
{
    public function __isset($property) : bool
    {
        return false;
    }
}
abstract class TestWithParameterAndReturnType
{
    public function returnString(): string {}
    public function returnInteger(): int {}
    public function returnFloat(): float {}
    public function returnBoolean(): bool {}
    public function returnArray(): array {}
    public function returnCallable(): callable {}
    public function returnGenerator(): \Generator {}
    public function withClassReturnType(): TestWithParameterAndReturnType {}
    public function withScalarParameters(int $integer, float $float, bool $boolean, string $string) {}
    public function returnSelf(): self {}
}
