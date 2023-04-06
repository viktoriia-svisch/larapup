<?php declare(strict_types=1);
namespace SebastianBergmann\Diff;
final class TimeEfficientImplementationTest extends LongestCommonSubsequenceTest
{
    protected function createImplementation(): LongestCommonSubsequenceCalculator
    {
        return new TimeEfficientLongestCommonSubsequenceCalculator;
    }
}
