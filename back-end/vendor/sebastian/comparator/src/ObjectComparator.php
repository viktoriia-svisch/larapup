<?php
namespace SebastianBergmann\Comparator;
class ObjectComparator extends ArrayComparator
{
    public function accepts($expected, $actual)
    {
        return \is_object($expected) && \is_object($actual);
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = [])
    {
        if (\get_class($actual) !== \get_class($expected)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual),
                false,
                \sprintf(
                    '%s is not instance of expected class "%s".',
                    $this->exporter->export($actual),
                    \get_class($expected)
                )
            );
        }
        if (\in_array([$actual, $expected], $processed, true) ||
            \in_array([$expected, $actual], $processed, true)) {
            return;
        }
        $processed[] = [$actual, $expected];
        if ($actual !== $expected) {
            try {
                parent::assertEquals(
                    $this->toArray($expected),
                    $this->toArray($actual),
                    $delta,
                    $canonicalize,
                    $ignoreCase,
                    $processed
                );
            } catch (ComparisonFailure $e) {
                throw new ComparisonFailure(
                    $expected,
                    $actual,
                    \substr_replace($e->getExpectedAsString(), \get_class($expected) . ' Object', 0, 5),
                    \substr_replace($e->getActualAsString(), \get_class($actual) . ' Object', 0, 5),
                    false,
                    'Failed asserting that two objects are equal.'
                );
            }
        }
    }
    protected function toArray($object)
    {
        return $this->exporter->toArray($object);
    }
}
