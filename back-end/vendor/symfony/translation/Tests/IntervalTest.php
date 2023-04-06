<?php
namespace Symfony\Component\Translation\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Interval;
class IntervalTest extends TestCase
{
    public function testTest($expected, $number, $interval)
    {
        $this->assertEquals($expected, Interval::test($number, $interval));
    }
    public function testTestException()
    {
        Interval::test(1, 'foobar');
    }
    public function getTests()
    {
        return [
            [true, 3, '{1,2, 3 ,4}'],
            [false, 10, '{1,2, 3 ,4}'],
            [false, 3, '[1,2]'],
            [true, 1, '[1,2]'],
            [true, 2, '[1,2]'],
            [false, 1, ']1,2['],
            [false, 2, ']1,2['],
            [true, log(0), '[-Inf,2['],
            [true, -log(0), '[-2,+Inf]'],
        ];
    }
}
