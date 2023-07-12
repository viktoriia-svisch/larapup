<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Util\InvalidArgumentHelper;
class MethodName extends StatelessInvocation
{
    private $constraint;
    public function __construct($constraint)
    {
        if (!$constraint instanceof Constraint) {
            if (!\is_string($constraint)) {
                throw InvalidArgumentHelper::factory(1, 'string');
            }
            $constraint = new IsEqual(
                $constraint,
                0,
                10,
                false,
                true
            );
        }
        $this->constraint = $constraint;
    }
    public function toString(): string
    {
        return 'method name ' . $this->constraint->toString();
    }
    public function matches(BaseInvocation $invocation)
    {
        return $this->constraint->evaluate($invocation->getMethodName(), '', true);
    }
}
