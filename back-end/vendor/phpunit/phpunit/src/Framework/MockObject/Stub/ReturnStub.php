<?php
namespace PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
use SebastianBergmann\Exporter\Exporter;
class ReturnStub implements Stub
{
    private $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function invoke(Invocation $invocation)
    {
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
