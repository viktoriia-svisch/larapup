<?php
namespace PHPUnit\Framework\Constraint;
class IsReadable extends Constraint
{
    public function toString(): string
    {
        return 'is readable';
    }
    protected function matches($other): bool
    {
        return \is_readable($other);
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            '"%s" is readable',
            $other
        );
    }
}
