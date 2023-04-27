<?php
namespace PHPUnit\Framework;
use SebastianBergmann\Comparator\ComparisonFailure;
class ExpectationFailedException extends AssertionFailedError
{
    protected $comparisonFailure;
    public function __construct(string $message, ComparisonFailure $comparisonFailure = null, \Exception $previous = null)
    {
        $this->comparisonFailure = $comparisonFailure;
        parent::__construct($message, 0, $previous);
    }
    public function getComparisonFailure(): ?ComparisonFailure
    {
        return $this->comparisonFailure;
    }
}
