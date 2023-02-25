<?php
namespace PHPUnit\Framework\Constraint;
class IsFinite extends Constraint
{
    public function toString(): string
    {
        return 'is finite';
    }
    protected function matches($other): bool
    {
        return \is_finite($other);
    }
}
