<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\TestCase;
class ExceptionMessageTest extends TestCase
{
    public function testLiteralMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A literal exception message');
        throw new \Exception('A literal exception message');
    }
    public function testPartialMessageBegin(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A partial');
        throw new \Exception('A partial exception message');
    }
    public function testPartialMessageMiddle(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('partial exception');
        throw new \Exception('A partial exception message');
    }
    public function testPartialMessageEnd(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exception message');
        throw new \Exception('A partial exception message');
    }
}
