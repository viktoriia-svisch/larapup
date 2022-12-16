<?php
namespace PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
use SebastianBergmann\Exporter\Exporter;
class ConsecutiveCalls implements Stub
{
    private $stack;
    private $value;
    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }
    public function invoke(Invocation $invocation)
    {
        $this->value = \array_shift($this->stack);
        if ($this->value instanceof Stub) {
            $this->value = $this->value->invoke($invocation);
        }
        return $this->value;
    }
    public function toString(): string
    {
        $exporter = new Exporter;
        return \sprintf(
            'return user-specified value %s',
            $exporter->export($this->value)
        );
    }
}
