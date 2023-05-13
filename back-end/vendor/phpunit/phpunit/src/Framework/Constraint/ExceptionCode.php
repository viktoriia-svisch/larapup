<?php
namespace PHPUnit\Framework\Constraint;
class ExceptionCode extends Constraint
{
    private $expectedCode;
    public function __construct($expected)
    {
        parent::__construct();
        $this->expectedCode = $expected;
    }
    public function toString(): string
    {
        return 'exception code is ';
    }
    protected function matches($other): bool
    {
        return (string) $other->getCode() === (string) $this->expectedCode;
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s is equal to expected exception code %s',
            $this->exporter->export($other->getCode()),
            $this->exporter->export($this->expectedCode)
        );
    }
}
