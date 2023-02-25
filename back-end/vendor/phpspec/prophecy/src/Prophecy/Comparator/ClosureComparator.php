<?php
namespace Prophecy\Comparator;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
final class ClosureComparator extends Comparator
{
    public function accepts($expected, $actual)
    {
        return is_object($expected) && $expected instanceof \Closure
            && is_object($actual) && $actual instanceof \Closure;
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        throw new ComparisonFailure(
            $expected,
            $actual,
            '',
            '',
            false,
            'all closures are born different'
        );
    }
}
