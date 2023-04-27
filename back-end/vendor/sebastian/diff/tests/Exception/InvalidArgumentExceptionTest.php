<?php declare(strict_types=1);
namespace SebastianBergmann\Diff;
use PHPUnit\Framework\TestCase;
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testInvalidArgumentException(): void
    {
        $previousException = new \LogicException();
        $message           = 'test';
        $code              = 123;
        $exception = new InvalidArgumentException($message, $code, $previousException);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
