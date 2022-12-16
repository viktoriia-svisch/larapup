<?php
namespace SebastianBergmann\Comparator;
class ResourceComparator extends Comparator
{
    public function accepts($expected, $actual)
    {
        return \is_resource($expected) && \is_resource($actual);
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if ($actual != $expected) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual)
            );
        }
    }
}
