<?php
namespace SebastianBergmann\Comparator;
use PHPUnit\Framework\TestCase;
final class ResourceComparatorTest extends TestCase
{
    private $comparator;
    protected function setUp(): void
    {
        $this->comparator = new ResourceComparator;
    }
    public function acceptsSucceedsProvider()
    {
        $tmpfile1 = \tmpfile();
        $tmpfile2 = \tmpfile();
        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
            [$tmpfile1, $tmpfile2]
        ];
    }
    public function acceptsFailsProvider()
    {
        $tmpfile1 = \tmpfile();
        return [
            [$tmpfile1, null],
            [null, $tmpfile1],
            [null, null]
        ];
    }
    public function assertEqualsSucceedsProvider()
    {
        $tmpfile1 = \tmpfile();
        $tmpfile2 = \tmpfile();
        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2]
        ];
    }
    public function assertEqualsFailsProvider()
    {
        $tmpfile1 = \tmpfile();
        $tmpfile2 = \tmpfile();
        return [
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1]
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
        $this->comparator->assertEquals($expected, $actual);
    }
}
