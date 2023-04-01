<?php
namespace SebastianBergmann\Comparator;
class SplObjectStorageComparator extends Comparator
{
    public function accepts($expected, $actual)
    {
        return $expected instanceof \SplObjectStorage && $actual instanceof \SplObjectStorage;
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        foreach ($actual as $object) {
            if (!$expected->contains($object)) {
                throw new ComparisonFailure(
                    $expected,
                    $actual,
                    $this->exporter->export($expected),
                    $this->exporter->export($actual),
                    false,
                    'Failed asserting that two objects are equal.'
                );
            }
        }
        foreach ($expected as $object) {
            if (!$actual->contains($object)) {
                throw new ComparisonFailure(
                    $expected,
                    $actual,
                    $this->exporter->export($expected),
                    $this->exporter->export($actual),
                    false,
                    'Failed asserting that two objects are equal.'
                );
            }
        }
    }
}
