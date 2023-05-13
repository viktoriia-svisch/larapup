<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
class InvokedAtIndex implements Invocation
{
    private $sequenceIndex;
    private $currentIndex = -1;
    public function __construct($sequenceIndex)
    {
        $this->sequenceIndex = $sequenceIndex;
    }
    public function toString(): string
    {
        return 'invoked at sequence index ' . $this->sequenceIndex;
    }
    public function matches(BaseInvocation $invocation)
    {
        $this->currentIndex++;
        return $this->currentIndex == $this->sequenceIndex;
    }
    public function invoked(BaseInvocation $invocation): void
    {
    }
    public function verify(): void
    {
        if ($this->currentIndex < $this->sequenceIndex) {
            throw new ExpectationFailedException(
                \sprintf(
                    'The expected invocation at index %s was never reached.',
                    $this->sequenceIndex
                )
            );
        }
    }
}
