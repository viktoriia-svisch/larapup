<?php
namespace PHPUnit\Framework\MockObject\Matcher;
class AnyInvokedCount extends InvokedRecorder
{
    public function toString(): string
    {
        return 'invoked zero or more times';
    }
    public function verify(): void
    {
    }
}
