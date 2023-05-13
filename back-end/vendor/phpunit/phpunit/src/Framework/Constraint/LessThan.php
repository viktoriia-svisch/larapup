<?php
namespace PHPUnit\Framework\Constraint;
class LessThan extends Constraint
{
    private $value;
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }
    public function toString(): string
    {
        return 'is less than ' . $this->exporter->export($this->value);
    }
    protected function matches($other): bool
    {
        return $this->value > $other;
    }
}
