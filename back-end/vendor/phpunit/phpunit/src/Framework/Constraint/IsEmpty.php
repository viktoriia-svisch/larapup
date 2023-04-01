<?php
namespace PHPUnit\Framework\Constraint;
use Countable;
class IsEmpty extends Constraint
{
    public function toString(): string
    {
        return 'is empty';
    }
    protected function matches($other): bool
    {
        if ($other instanceof Countable) {
            return \count($other) === 0;
        }
        return empty($other);
    }
    protected function failureDescription($other): string
    {
        $type = \gettype($other);
        return \sprintf(
            '%s %s %s',
            $type[0] == 'a' || $type[0] == 'o' ? 'an' : 'a',
            $type,
            $this->toString()
        );
    }
}
