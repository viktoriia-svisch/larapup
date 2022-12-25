<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\ExpectationFailedException;
class InvokedAtLeastCount extends InvokedRecorder
{
    private $requiredInvocations;
    public function __construct($requiredInvocations)
    {
        $this->requiredInvocations = $requiredInvocations;
    }
    public function toString(): string
    {
        return 'invoked at least ' . $this->requiredInvocations . ' times';
    }
    public function verify(): void
    {
        $count = $this->getInvocationCount();
        if ($count < $this->requiredInvocations) {
            throw new ExpectationFailedException(
                'Expected invocation at least ' . $this->requiredInvocations .
                ' times but it occurred ' . $count . ' time(s).'
            );
        }
    }
}
