<?php
namespace PHPUnit\Framework\Constraint;
class StringStartsWith extends Constraint
{
    private $prefix;
    public function __construct(string $prefix)
    {
        parent::__construct();
        $this->prefix = $prefix;
    }
    public function toString(): string
    {
        return 'starts with "' . $this->prefix . '"';
    }
    protected function matches($other): bool
    {
        return \strpos($other, $this->prefix) === 0;
    }
}
