<?php
namespace PHPUnit\Framework\Constraint;
class IsNull extends Constraint
{
    public function toString(): string
    {
        return 'is null';
    }
    protected function matches($other): bool
    {
        return $other === null;
    }
}
