<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
class DeferredError extends StatelessInvocation
{
    private $exception;
    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }
    public function verify(): void
    {
        throw $this->exception;
    }
    public function toString(): string
    {
        return '';
    }
    public function matches(BaseInvocation $invocation): bool
    {
        return true;
    }
}
