<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
class ArraySubset extends Constraint
{
    private $subset;
    private $strict;
    public function __construct(iterable $subset, bool $strict = false)
    {
        parent::__construct();
        $this->strict = $strict;
        $this->subset = $subset;
    }
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $other        = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);
        $patched = \array_replace_recursive($other, $this->subset);
        if ($this->strict) {
            $result = $other === $patched;
        } else {
            $result = $other == $patched;
        }
        if ($returnResult) {
            return $result;
        }
        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                \var_export($patched, true),
                \var_export($other, true)
            );
            $this->fail($other, $description, $f);
        }
    }
    public function toString(): string
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
    }
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }
    private function toArray(iterable $other): array
    {
        if (\is_array($other)) {
            return $other;
        }
        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }
        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }
        return (array) $other;
    }
}
