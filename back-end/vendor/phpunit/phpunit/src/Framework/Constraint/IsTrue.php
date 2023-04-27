<?php
namespace PHPUnit\Framework\Constraint;
class IsTrue extends Constraint
{
    public function toString(): string
    {
        return 'is true';
    }
    protected function matches($other): bool
    {
        return $other === true;
    }
}
