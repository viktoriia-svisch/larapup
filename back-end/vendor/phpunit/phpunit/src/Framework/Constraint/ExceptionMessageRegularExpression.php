<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Util\RegularExpression as RegularExpressionUtil;
class ExceptionMessageRegularExpression extends Constraint
{
    private $expectedMessageRegExp;
    public function __construct(string $expected)
    {
        parent::__construct();
        $this->expectedMessageRegExp = $expected;
    }
    public function toString(): string
    {
        return 'exception message matches ';
    }
    protected function matches($other): bool
    {
        $match = RegularExpressionUtil::safeMatch($this->expectedMessageRegExp, $other->getMessage());
        if ($match === false) {
            throw new \PHPUnit\Framework\Exception(
                "Invalid expected exception message regex given: '{$this->expectedMessageRegExp}'"
            );
        }
        return $match === 1;
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            "exception message '%s' matches '%s'",
            $other->getMessage(),
            $this->expectedMessageRegExp
        );
    }
}
