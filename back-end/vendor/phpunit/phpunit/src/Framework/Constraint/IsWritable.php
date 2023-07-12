<?php
namespace PHPUnit\Framework\Constraint;
class IsWritable extends Constraint
{
    public function toString(): string
    {
        return 'is writable';
    }
    protected function matches($other): bool
    {
        return \is_writable($other);
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            '"%s" is writable',
            $other
        );
    }
}
