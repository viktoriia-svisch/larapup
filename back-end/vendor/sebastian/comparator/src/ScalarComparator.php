<?php
namespace SebastianBergmann\Comparator;
class ScalarComparator extends Comparator
{
    public function accepts($expected, $actual)
    {
        return ((\is_scalar($expected) xor null === $expected) &&
               (\is_scalar($actual) xor null === $actual))
               || (\is_string($expected) && \is_object($actual) && \method_exists($actual, '__toString'))
               || (\is_object($expected) && \method_exists($expected, '__toString') && \is_string($actual));
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $expectedToCompare = $expected;
        $actualToCompare   = $actual;
        if (\is_string($expected) || \is_string($actual)) {
            $expectedToCompare = (string) $expectedToCompare;
            $actualToCompare   = (string) $actualToCompare;
            if ($ignoreCase) {
                $expectedToCompare = \strtolower($expectedToCompare);
                $actualToCompare   = \strtolower($actualToCompare);
            }
        }
        if ($expectedToCompare !== $actualToCompare && \is_string($expected) && \is_string($actual)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual),
                false,
                'Failed asserting that two strings are equal.'
            );
        }
        if ($expectedToCompare != $actualToCompare) {
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
