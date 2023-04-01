<?php
namespace SebastianBergmann\Comparator;
class DateTimeComparator extends ObjectComparator
{
    public function accepts($expected, $actual)
    {
        return ($expected instanceof \DateTime || $expected instanceof \DateTimeInterface) &&
               ($actual instanceof \DateTime || $actual instanceof \DateTimeInterface);
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = [])
    {
        $absDelta = \abs($delta);
        $delta    = new \DateInterval(\sprintf('PT%dS', $absDelta));
        $delta->f = $absDelta - \floor($absDelta);
        $actualClone = (clone $actual)
            ->setTimezone(new \DateTimeZone('UTC'));
        $expectedLower = (clone $expected)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->sub($delta);
        $expectedUpper = (clone $expected)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->add($delta);
        if ($actualClone < $expectedLower || $actualClone > $expectedUpper) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->dateTimeToString($expected),
                $this->dateTimeToString($actual),
                false,
                'Failed asserting that two DateTime objects are equal.'
            );
        }
    }
    private function dateTimeToString(\DateTimeInterface $datetime): string
    {
        $string = $datetime->format('Y-m-d\TH:i:s.uO');
        return $string ?: 'Invalid DateTimeInterface object';
    }
}
