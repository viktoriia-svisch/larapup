<?php
namespace PHPUnit\Framework\Constraint;
class DirectoryExists extends Constraint
{
    public function toString(): string
    {
        return 'directory exists';
    }
    protected function matches($other): bool
    {
        return \is_dir($other);
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            'directory "%s" exists',
            $other
        );
    }
}
