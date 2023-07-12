<?php
namespace PHPUnit\Framework;
use PHPUnit\Framework\Error\Error;
use Throwable;
class TestFailure
{
    protected $failedTest;
    protected $thrownException;
    private $testName;
    public static function exceptionToString(Throwable $e): string
    {
        if ($e instanceof SelfDescribing) {
            $buffer = $e->toString();
            if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
                $buffer .= $e->getComparisonFailure()->getDiff();
            }
            if (!empty($buffer)) {
                $buffer = \trim($buffer) . "\n";
            }
            return $buffer;
        }
        if ($e instanceof Error) {
            return $e->getMessage() . "\n";
        }
        if ($e instanceof ExceptionWrapper) {
            return $e->getClassName() . ': ' . $e->getMessage() . "\n";
        }
        return \get_class($e) . ': ' . $e->getMessage() . "\n";
    }
    public function __construct(Test $failedTest, $t)
    {
        if ($failedTest instanceof SelfDescribing) {
            $this->testName = $failedTest->toString();
        } else {
            $this->testName = \get_class($failedTest);
        }
        if (!$failedTest instanceof TestCase || !$failedTest->isInIsolation()) {
            $this->failedTest = $failedTest;
        }
        $this->thrownException = $t;
    }
    public function toString(): string
    {
        return \sprintf(
            '%s: %s',
            $this->testName,
            $this->thrownException->getMessage()
        );
    }
    public function getExceptionAsString(): string
    {
        return self::exceptionToString($this->thrownException);
    }
    public function getTestName(): string
    {
        return $this->testName;
    }
    public function failedTest(): ?Test
    {
        return $this->failedTest;
    }
    public function thrownException(): Throwable
    {
        return $this->thrownException;
    }
    public function exceptionMessage(): string
    {
        return $this->thrownException()->getMessage();
    }
    public function isFailure(): bool
    {
        return $this->thrownException() instanceof AssertionFailedError;
    }
}
