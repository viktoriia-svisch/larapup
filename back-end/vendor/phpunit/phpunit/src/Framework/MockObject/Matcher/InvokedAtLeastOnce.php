<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\ExpectationFailedException;
class InvokedAtLeastOnce extends InvokedRecorder
{
    public function toString(): string
    {
        return 'invoked at least once';
    }
    public function verify(): void
    {
        $count = $this->getInvocationCount();
        if ($count < 1) {
            throw new ExpectationFailedException(
                'Expected invocation at least once but it never occurred.'
            );
        }
    }
}
