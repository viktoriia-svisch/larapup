<?php
namespace PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
class ReturnArgument implements Stub
{
    private $argumentIndex;
    public function __construct($argumentIndex)
    {
        $this->argumentIndex = $argumentIndex;
    }
    public function invoke(Invocation $invocation)
    {
        if (isset($invocation->getParameters()[$this->argumentIndex])) {
            return $invocation->getParameters()[$this->argumentIndex];
        }
    }
    public function toString(): string
    {
        return \sprintf('return argument #%d', $this->argumentIndex);
    }
}
