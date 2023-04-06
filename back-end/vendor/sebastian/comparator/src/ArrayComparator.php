<?php
namespace SebastianBergmann\Comparator;
class ArrayComparator extends Comparator
{
    public function accepts($expected, $actual)
    {
        return \is_array($expected) && \is_array($actual);
    }
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = [])
    {
        if ($canonicalize) {
            \sort($expected);
            \sort($actual);
        }
        $remaining        = $actual;
        $actualAsString   = "Array (\n";
        $expectedAsString = "Array (\n";
        $equal            = true;
        foreach ($expected as $key => $value) {
            unset($remaining[$key]);
            if (!\array_key_exists($key, $actual)) {
                $expectedAsString .= \sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($key),
                    $this->exporter->shortenedExport($value)
                );
                $equal = false;
                continue;
            }
            try {
                $comparator = $this->factory->getComparatorFor($value, $actual[$key]);
                $comparator->assertEquals($value, $actual[$key], $delta, $canonicalize, $ignoreCase, $processed);
                $expectedAsString .= \sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($key),
                    $this->exporter->shortenedExport($value)
                );
                $actualAsString .= \sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($key),
                    $this->exporter->shortenedExport($actual[$key])
                );
            } catch (ComparisonFailure $e) {
                $expectedAsString .= \sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($key),
                    $e->getExpectedAsString() ? $this->indent($e->getExpectedAsString()) : $this->exporter->shortenedExport($e->getExpected())
                );
                $actualAsString .= \sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($key),
                    $e->getActualAsString() ? $this->indent($e->getActualAsString()) : $this->exporter->shortenedExport($e->getActual())
                );
                $equal = false;
            }
        }
        foreach ($remaining as $key => $value) {
            $actualAsString .= \sprintf(
                "    %s => %s\n",
                $this->exporter->export($key),
                $this->exporter->shortenedExport($value)
            );
            $equal = false;
        }
        $expectedAsString .= ')';
        $actualAsString .= ')';
        if (!$equal) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                false,
                'Failed asserting that two arrays are equal.'
            );
        }
    }
    protected function indent($lines)
    {
        return \trim(\str_replace("\n", "\n    ", $lines));
    }
}
