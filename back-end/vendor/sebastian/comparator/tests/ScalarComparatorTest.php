<?php
namespace SebastianBergmann\Comparator;
use PHPUnit\Framework\TestCase;
final class ScalarComparatorTest extends TestCase
{
    private $comparator;
    protected function setUp(): void
    {
        $this->comparator = new ScalarComparator;
    }
    public function acceptsSucceedsProvider()
    {
        return [
            ['string', 'string'],
            [new ClassWithToString, 'string'],
            ['string', new ClassWithToString],
            ['string', null],
            [false, 'string'],
            [false, true],
            [null, false],
            [null, null],
            ['10', 10],
            ['', false],
            ['1', true],
            [1, true],
            [0, false],
            [0.1, '0.1']
        ];
    }
    public function acceptsFailsProvider()
    {
        return [
            [[], []],
            ['string', []],
            [new ClassWithToString, new ClassWithToString],
            [false, new ClassWithToString],
            [\tmpfile(), \tmpfile()]
        ];
    }
    public function assertEqualsSucceedsProvider()
    {
        return [
            ['string', 'string'],
            [new ClassWithToString, new ClassWithToString],
            ['string representation', new ClassWithToString],
            [new ClassWithToString, 'string representation'],
            ['string', 'STRING', true],
            ['STRING', 'string', true],
            ['String Representation', new ClassWithToString, true],
            [new ClassWithToString, 'String Representation', true],
            ['10', 10],
            ['', false],
            ['1', true],
            [1, true],
            [0, false],
            [0.1, '0.1'],
            [false, null],
            [false, false],
            [true, true],
            [null, null]
        ];
    }
    public function assertEqualsFailsProvider()
    {
        $stringException = 'Failed asserting that two strings are equal.';
        $otherException  = 'matches expected';
        return [
            ['string', 'other string', $stringException],
            ['string', 'STRING', $stringException],
            ['STRING', 'string', $stringException],
            ['string', 'other string', $stringException],
            ['9E6666666', '9E7777777', $stringException],
            [new ClassWithToString, 'does not match', $otherException],
            ['does not match', new ClassWithToString, $otherException],
            [0, 'Foobar', $otherException],
            ['Foobar', 0, $otherException],
            ['10', 25, $otherException],
            ['1', false, $otherException],
            ['', true, $otherException],
            [false, true, $otherException],
            [true, false, $otherException],
            [null, true, $otherException],
            [0, true, $otherException],
            ['0', '0.0', $stringException],
            ['0.', '0.0', $stringException],
            ['0e1', '0e2', $stringException],
            ["\n\n\n0.0", '                   0.', $stringException],
            ['0.0', '25e-10000', $stringException],
        ];
    }
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
          $this->comparator->accepts($expected, $actual)
        );
    }
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
          $this->comparator->accepts($expected, $actual)
        );
    }
    public function testAssertEqualsSucceeds($expected, $actual, $ignoreCase = false): void
    {
        $exception = null;
        try {
            $this->comparator->assertEquals($expected, $actual, 0.0, false, $ignoreCase);
        } catch (ComparisonFailure $exception) {
        }
        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
    public function testAssertEqualsFails($expected, $actual, $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);
        $this->comparator->assertEquals($expected, $actual);
    }
}
