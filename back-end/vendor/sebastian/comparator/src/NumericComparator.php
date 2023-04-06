<?php
namespace SebastianBergmann\Comparator;
class NumericComparator extends ScalarComparator
{
    public function accepts($expected, $actual)
    {
        return \is_numeric($expected) && \is_numeric($actual) &&
               !(\is_float($expected) || \is_float($actual)) &&
               !(\is_string($expected) && \is_string($actual));
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if (\is_infinite($actual) && \is_infinite($expected)) {
            return; 
        }
        if ((\is_infinite($actual) xor \is_infinite($expected)) ||
            (\is_nan($actual) || \is_nan($expected)) ||
            \abs($actual - $expected) > $delta) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                '',
                '',
                false,
                \sprintf(
                    'Failed asserting that %s matches expected %s.',
                    $this->exporter->export($actual),
                    $this->exporter->export($expected)
                )
            );
        }
    }
}
