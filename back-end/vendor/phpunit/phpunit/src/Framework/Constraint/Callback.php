<?php
namespace PHPUnit\Framework\Constraint;
class Callback extends Constraint
{
    private $callback;
    public function __construct(callable $callback)
    {
        parent::__construct();
        $this->callback = $callback;
    }
    public function toString(): string
    {
        return 'is accepted by specified callback';
    }
    protected function matches($other): bool
    {
        return \call_user_func($this->callback, $other);
    }
}
