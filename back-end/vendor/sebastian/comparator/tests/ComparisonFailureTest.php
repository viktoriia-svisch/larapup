<?php
namespace SebastianBergmann\Comparator;
use PHPUnit\Framework\TestCase;
final class ComparisonFailureTest extends TestCase
{
    public function testComparisonFailure(): void
    {
        $actual   = "\nB\n";
        $expected = "\nA\n";
        $message  = 'Test message';
        $failure = new ComparisonFailure(
            $expected,
            $actual,
            '|' . $expected,
            '|' . $actual,
            false,
            $message
        );
        $this->assertSame($actual, $failure->getActual());
        $this->assertSame($expected, $failure->getExpected());
        $this->assertSame('|' . $actual, $failure->getActualAsString());
        $this->assertSame('|' . $expected, $failure->getExpectedAsString());
        $diff = '
--- Expected
+++ Actual
@@ @@
 |
-A
+B
';
        $this->assertSame($diff, $failure->getDiff());
        $this->assertSame($message . $diff, $failure->toString());
    }
    public function testDiffNotPossible(): void
    {
        $failure = new ComparisonFailure('a', 'b', false, false, true, 'test');
        $this->assertSame('', $failure->getDiff());
        $this->assertSame('test', $failure->toString());
    }
}
