<?php declare(strict_types=1);
namespace PHPUnit\Util;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
final class TextTestListRenderer
{
    public function render(TestSuite $suite): string
    {
        $buffer = 'Available test(s):' . \PHP_EOL;
        foreach (new \RecursiveIteratorIterator($suite->getIterator()) as $test) {
            if ($test instanceof TestCase) {
                $name = \sprintf(
                    '%s::%s',
                    \get_class($test),
                    \str_replace(' with data set ', '', $test->getName())
                );
            } elseif ($test instanceof PhptTestCase) {
                $name = $test->getName();
            } else {
                continue;
            }
            $buffer .= \sprintf(
                ' - %s' . \PHP_EOL,
                $name
            );
        }
        return $buffer;
    }
}
