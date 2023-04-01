<?php
namespace PHPUnit\Framework\Constraint;
class IsNan extends Constraint
{
    public function toString(): string
    {
        return 'is nan';
    }
    protected function matches($other): bool
    {
        return \is_nan($other);
    }
}
