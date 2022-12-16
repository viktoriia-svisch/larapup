<?php
namespace PHPUnit\Framework\Constraint;
class FileExists extends Constraint
{
    public function toString(): string
    {
        return 'file exists';
    }
    protected function matches($other): bool
    {
        return \file_exists($other);
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            'file "%s" exists',
            $other
        );
    }
}
