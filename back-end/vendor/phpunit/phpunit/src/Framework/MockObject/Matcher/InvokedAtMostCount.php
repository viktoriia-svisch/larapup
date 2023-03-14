<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\ExpectationFailedException;
class InvokedAtMostCount extends InvokedRecorder
{
    private $allowedInvocations;
    public function __construct($allowedInvocations)
    {
        $this->allowedInvocations = $allowedInvocations;
    }
    public function toString(): string
    {
        return 'invoked at most ' . $this->allowedInvocations . ' times';
    }
    public function verify(): void
    {
        $count = $this->getInvocationCount();
        if ($count > $this->allowedInvocations) {
            throw new ExpectationFailedException(
                'Expected invocation at most ' . $this->allowedInvocations .
                ' times but it occurred ' . $count . ' time(s).'
            );
        }
    }
}
