<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
class IsAnything extends Constraint
{
    public function evaluate($other, $description = '', $returnResult = false)
    {
        return $returnResult ? true : null;
    }
    public function toString(): string
    {
        return 'is anything';
    }
    public function count(): int
    {
        return 0;
    }
}
