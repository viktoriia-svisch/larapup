<?php
namespace PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
use SebastianBergmann\Exporter\Exporter;
class ReturnReference implements Stub
{
    private $reference;
    public function __construct(&$reference)
    {
        $this->reference = &$reference;
    }
    public function invoke(Invocation $invocation)
    {
        return $this->reference;
    }
    public function toString(): string
    {
        $exporter = new Exporter;
        return \sprintf(
            'return user-specified reference %s',
            $exporter->export($this->reference)
        );
    }
}
