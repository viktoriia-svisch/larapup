<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
class IsIdentical extends Constraint
{
    private const EPSILON = 0.0000000001;
    private $value;
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (\is_float($this->value) && \is_float($other) &&
            !\is_infinite($this->value) && !\is_infinite($other) &&
            !\is_nan($this->value) && !\is_nan($other)) {
            $success = \abs($this->value - $other) < self::EPSILON;
        } else {
            $success = $this->value === $other;
        }
        if ($returnResult) {
            return $success;
        }
        if (!$success) {
            $f = null;
            if (\is_string($this->value) && \is_string($other)) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    \sprintf("'%s'", $this->value),
                    \sprintf("'%s'", $other)
                );
            }
            if (\is_array($this->value) && \is_array($other)) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    $this->exporter->export($this->value),
                    $this->exporter->export($other)
                );
            }
            $this->fail($other, $description, $f);
        }
    }
    public function toString(): string
    {
        if (\is_object($this->value)) {
            return 'is identical to an object of class "' .
                \get_class($this->value) . '"';
        }
        return 'is identical to ' . $this->exporter->export($this->value);
    }
    protected function failureDescription($other): string
    {
        if (\is_object($this->value) && \is_object($other)) {
            return 'two variables reference the same object';
        }
        if (\is_string($this->value) && \is_string($other)) {
            return 'two strings are identical';
        }
        if (\is_array($this->value) && \is_array($other)) {
            return 'two arrays are identical';
        }
        return parent::failureDescription($other);
    }
}
