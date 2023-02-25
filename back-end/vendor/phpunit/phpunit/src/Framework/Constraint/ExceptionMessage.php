<?php
namespace PHPUnit\Framework\Constraint;
class ExceptionMessage extends Constraint
{
    private $expectedMessage;
    public function __construct(string $expected)
    {
        parent::__construct();
        $this->expectedMessage = $expected;
    }
    public function toString(): string
    {
        if ($this->expectedMessage === '') {
            return 'exception message is empty';
        }
        return 'exception message contains ';
    }
    protected function matches($other): bool
    {
        if ($this->expectedMessage === '') {
            return $other->getMessage() === '';
        }
        return \strpos($other->getMessage(), $this->expectedMessage) !== false;
    }
    protected function failureDescription($other): string
    {
        if ($this->expectedMessage === '') {
            return \sprintf(
                "exception message is empty but is '%s'",
                $other->getMessage()
            );
        }
        return \sprintf(
            "exception message '%s' contains '%s'",
            $other->getMessage(),
            $this->expectedMessage
        );
    }
}
