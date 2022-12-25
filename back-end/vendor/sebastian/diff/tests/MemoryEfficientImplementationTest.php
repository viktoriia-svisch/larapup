<?php declare(strict_types=1);
namespace SebastianBergmann\Diff;
final class MemoryEfficientImplementationTest extends LongestCommonSubsequenceTest
{
    protected function createImplementation(): LongestCommonSubsequenceCalculator
    {
        return new MemoryEfficientLongestCommonSubsequenceCalculator;
    }
}
