<?php
namespace PHPUnit\Framework\Constraint;
class StringEndsWith extends Constraint
{
    private $suffix;
    public function __construct(string $suffix)
    {
        parent::__construct();
        $this->suffix = $suffix;
    }
    public function toString(): string
    {
        return 'ends with "' . $this->suffix . '"';
    }
    protected function matches($other): bool
    {
        return \substr($other, 0 - \strlen($this->suffix)) === $this->suffix;
    }
}
