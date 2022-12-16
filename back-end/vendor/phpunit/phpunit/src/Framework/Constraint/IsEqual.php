<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
class IsEqual extends Constraint
{
    private $value;
    private $delta;
    private $maxDepth;
    private $canonicalize;
    private $ignoreCase;
    public function __construct($value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false)
    {
        parent::__construct();
        $this->value        = $value;
        $this->delta        = $delta;
        $this->maxDepth     = $maxDepth;
        $this->canonicalize = $canonicalize;
        $this->ignoreCase   = $ignoreCase;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if ($this->value === $other) {
            return true;
        }
        $comparatorFactory = ComparatorFactory::getInstance();
        try {
            $comparator = $comparatorFactory->getComparatorFor(
                $this->value,
                $other
            );
            $comparator->assertEquals(
                $this->value,
                $other,
                $this->delta,
                $this->canonicalize,
                $this->ignoreCase
            );
        } catch (ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }
            throw new ExpectationFailedException(
                \trim($description . "\n" . $f->getMessage()),
                $f
            );
        }
        return true;
    }
    public function toString(): string
    {
        $delta = '';
        if (\is_string($this->value)) {
            if (\strpos($this->value, "\n") !== false) {
                return 'is equal to <text>';
            }
            return \sprintf(
                "is equal to '%s'",
                $this->value
            );
        }
        if ($this->delta != 0) {
            $delta = \sprintf(
                ' with delta <%F>',
                $this->delta
            );
        }
        return \sprintf(
            'is equal to %s%s',
            $this->exporter->export($this->value),
            $delta
        );
    }
}
