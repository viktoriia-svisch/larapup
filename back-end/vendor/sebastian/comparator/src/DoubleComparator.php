<?php
namespace SebastianBergmann\Comparator;
class DoubleComparator extends NumericComparator
{
    const EPSILON = 0.0000000001;
    public function accepts($expected, $actual)
    {
        return (\is_float($expected) || \is_float($actual)) && \is_numeric($expected) && \is_numeric($actual);
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if ($delta == 0) {
            $delta = self::EPSILON;
        }
        parent::assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);
    }
}
