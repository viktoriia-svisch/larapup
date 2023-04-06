<?php
namespace SebastianBergmann\Comparator;
class TypeComparator extends Comparator
{
    public function accepts($expected, $actual)
    {
        return true;
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if (\gettype($expected) != \gettype($actual)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                '',
                '',
                false,
                \sprintf(
                    '%s does not match expected type "%s".',
                    $this->exporter->shortenedExport($actual),
                    \gettype($expected)
                )
            );
        }
    }
}
