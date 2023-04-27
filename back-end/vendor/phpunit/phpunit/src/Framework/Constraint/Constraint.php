<?php
namespace PHPUnit\Framework\Constraint;
use Countable;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\SelfDescribing;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Exporter\Exporter;
abstract class Constraint implements Countable, SelfDescribing
{
    protected $exporter;
    public function __construct()
    {
        $this->exporter = new Exporter;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = false;
        if ($this->matches($other)) {
            $success = true;
        }
        if ($returnResult) {
            return $success;
        }
        if (!$success) {
            $this->fail($other, $description);
        }
    }
    public function count(): int
    {
        return 1;
    }
    protected function matches($other): bool
    {
        return false;
    }
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
    {
        $failureDescription = \sprintf(
            'Failed asserting that %s.',
            $this->failureDescription($other)
        );
        $additionalFailureDescription = $this->additionalFailureDescription($other);
        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }
        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }
        throw new ExpectationFailedException(
            $failureDescription,
            $comparisonFailure
        );
    }
    protected function additionalFailureDescription($other): string
    {
        return '';
    }
    protected function failureDescription($other): string
    {
        return $this->exporter->export($other) . ' ' . $this->toString();
    }
}
