<?php
namespace SebastianBergmann\Comparator;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
final class DateTimeComparatorTest extends TestCase
{
    private $comparator;
    protected function setUp(): void
    {
        $this->comparator = new DateTimeComparator;
    }
    public function acceptsFailsProvider()
    {
        $datetime = new DateTime;
        return [
            [$datetime, null],
            [null, $datetime],
            [null, null]
        ];
    }
    public function assertEqualsSucceedsProvider()
    {
        return [
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:25', new DateTimeZone('America/New_York')),
                10
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:14:40', new DateTimeZone('America/New_York')),
                65
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29', new DateTimeZone('America/New_York'))
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/Chicago'))
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:49', new DateTimeZone('America/Chicago')),
                15
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago'))
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
                100
            ],
            [
                new DateTime('@1364616000'),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago'))
            ],
            [
                new DateTime('2013-03-29T05:13:35-0500'),
                new DateTime('2013-03-29T04:13:35-0600')
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
                100
            ],
            [
                new DateTimeImmutable('2013-03-30 12:00:00', new DateTimeZone('UTC')),
                new DateTimeImmutable('2013-03-30 12:00:00.5', new DateTimeZone('UTC')),
                0.5
            ],
        ];
    }
    public function assertEqualsFailsProvider()
    {
        return [
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York'))
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
                3500
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
                3500
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York'))
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                43200
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
                3500
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/Chicago'))
            ],
            [
                new DateTime('2013-03-29T05:13:35-0600'),
                new DateTime('2013-03-29T04:13:35-0600')
            ],
            [
                new DateTime('2013-03-29T05:13:35-0600'),
                new DateTime('2013-03-29T05:13:35-0500')
            ],
        ];
    }
    public function testAcceptsSucceeds(): void
    {
        $this->assertTrue(
            $this->comparator->accepts(
                new DateTime,
                new DateTime
            )
        );
    }
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual)
        );
    }
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0): void
    {
        $exception = null;
        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }
        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
    public function testAssertEqualsFails($expected, $actual, $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DateTime objects are equal.');
        $this->comparator->assertEquals($expected, $actual, $delta);
    }
    public function testAcceptsDateTimeInterface(): void
    {
        $this->assertTrue($this->comparator->accepts(new DateTime, new DateTimeImmutable));
    }
    public function testSupportsDateTimeInterface(): void
    {
        $this->assertNull(
            $this->comparator->assertEquals(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York'))
            )
        );
    }
}