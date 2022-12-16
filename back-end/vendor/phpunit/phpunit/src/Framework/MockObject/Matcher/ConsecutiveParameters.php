<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
class ConsecutiveParameters extends StatelessInvocation
{
    private $parameterGroups = [];
    private $invocations = [];
    public function __construct(array $parameterGroups)
    {
        foreach ($parameterGroups as $index => $parameters) {
            foreach ($parameters as $parameter) {
                if (!$parameter instanceof Constraint) {
                    $parameter = new IsEqual($parameter);
                }
                $this->parameterGroups[$index][] = $parameter;
            }
        }
    }
    public function toString(): string
    {
        return 'with consecutive parameters';
    }
    public function matches(BaseInvocation $invocation)
    {
        $this->invocations[] = $invocation;
        $callIndex           = \count($this->invocations) - 1;
        $this->verifyInvocation($invocation, $callIndex);
        return false;
    }
    public function verify(): void
    {
        foreach ($this->invocations as $callIndex => $invocation) {
            $this->verifyInvocation($invocation, $callIndex);
        }
    }
    private function verifyInvocation(BaseInvocation $invocation, $callIndex): void
    {
        if (!isset($this->parameterGroups[$callIndex])) {
            return;
        }
        if ($invocation === null) {
            throw new ExpectationFailedException(
                'Mocked method does not exist.'
            );
        }
        $parameters = $this->parameterGroups[$callIndex];
        if (\count($invocation->getParameters()) < \count($parameters)) {
            throw new ExpectationFailedException(
                \sprintf(
                    'Parameter count for invocation %s is too low.',
                    $invocation->toString()
                )
            );
        }
        foreach ($parameters as $i => $parameter) {
            $parameter->evaluate(
                $invocation->getParameters()[$i],
                \sprintf(
                    'Parameter %s for invocation #%d %s does not match expected ' .
                    'value.',
                    $i,
                    $callIndex,
                    $invocation->toString()
                )
            );
        }
    }
}
