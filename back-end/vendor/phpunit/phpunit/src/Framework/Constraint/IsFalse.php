<?php
namespace PHPUnit\Framework\Constraint;
class IsFalse extends Constraint
{
    public function toString(): string
    {
        return 'is false';
    }
    protected function matches($other): bool
    {
        return $other === false;
    }
}
