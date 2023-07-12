<?php
namespace PHPUnit\Framework\Constraint;
class IsInfinite extends Constraint
{
    public function toString(): string
    {
        return 'is infinite';
    }
    protected function matches($other): bool
    {
        return \is_infinite($other);
    }
}
