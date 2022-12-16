<?php
namespace SebastianBergmann\Comparator;
use PHPUnit\Framework\TestCase;
use stdClass;
final class TypeComparatorTest extends TestCase
{
    private $comparator;
    protected function setUp(): void
    {
        $this->comparator = new TypeComparator;
    }
    public function acceptsSucceedsProvider()
    {
        return [
            [true, 1],
            [false, [1]],
            [null, new stdClass],
            [1.0, 5],
            ['', '']
        ];
    }
    public function assertEqualsSucceedsProvider()
    {
        return [
            [true, true],
            [true, false],
            [false, false],
            [null, null],
            [new stdClass, new stdClass],
            [0, 0],
            [1.0, 2.0],
            ['hello', 'world'],
            ['', ''],
            [[], [1, 2, 3]]
        ];
    }
    public function assertEqualsFailsProvider()
    {
        return [
            [true, null],
            [null, false],
            [1.0, 0],
            [new stdClass, []],
            ['1', 1]
        ];
    }
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
          $this->comparator->accepts($expected, $actual)
        );
    }
    public function testAssertEqualsSucceeds($expected, $actual): void
    {
        $exception = null;
        try {
            $this->comparator->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }
        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
    public function testAssertEqualsFails($expected, $actual): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('does not match expected type');
        $this->comparator->assertEquals($expected, $actual);
    }
}
