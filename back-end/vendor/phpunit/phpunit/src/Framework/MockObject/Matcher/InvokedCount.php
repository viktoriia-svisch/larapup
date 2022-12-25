<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
class InvokedCount extends InvokedRecorder
{
    private $expectedCount;
    public function __construct($expectedCount)
    {
        $this->expectedCount = $expectedCount;
    }
    public function isNever()
    {
        return $this->expectedCount === 0;
    }
    public function toString(): string
    {
        return 'invoked ' . $this->expectedCount . ' time(s)';
    }
    public function invoked(BaseInvocation $invocation): void
    {
        parent::invoked($invocation);
        $count = $this->getInvocationCount();
        if ($count > $this->expectedCount) {
            $message = $invocation->toString() . ' ';
            switch ($this->expectedCount) {
                case 0:
                    $message .= 'was not expected to be called.';
                    break;
                case 1:
                    $message .= 'was not expected to be called more than once.';
                    break;
                default:
                    $message .= \sprintf(
                        'was not expected to be called more than %d times.',
                        $this->expectedCount
                    );
            }
            throw new ExpectationFailedException($message);
        }
    }
    public function verify(): void
    {
        $count = $this->getInvocationCount();
        if ($count !== $this->expectedCount) {
            throw new ExpectationFailedException(
                \sprintf(
                    'Method was expected to be called %d times, ' .
                    'actually called %d times.',
                    $this->expectedCount,
                    $count
                )
            );
        }
    }
}
