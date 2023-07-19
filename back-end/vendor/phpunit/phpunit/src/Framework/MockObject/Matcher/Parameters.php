<?php
namespace PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
class Parameters extends StatelessInvocation
{
    private $parameters = [];
    private $invocation;
    private $parameterVerificationResult;
    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (!($parameter instanceof Constraint)) {
                $parameter = new IsEqual(
                    $parameter
                );
            }
            $this->parameters[] = $parameter;
        }
    }
    public function toString(): string
    {
        $text = 'with parameter';
        foreach ($this->parameters as $index => $parameter) {
            if ($index > 0) {
                $text .= ' and';
            }
            $text .= ' ' . $index . ' ' . $parameter->toString();
        }
        return $text;
    }
    public function matches(BaseInvocation $invocation)
    {
        $this->invocation                  = $invocation;
        $this->parameterVerificationResult = null;
        try {
            $this->parameterVerificationResult = $this->verify();
            return $this->parameterVerificationResult;
        } catch (ExpectationFailedException $e) {
            $this->parameterVerificationResult = $e;
            throw $this->parameterVerificationResult;
        }
    }
    public function verify()
    {
        if (isset($this->parameterVerificationResult)) {
            return $this->guardAgainstDuplicateEvaluationOfParameterConstraints();
        }
        if ($this->invocation === null) {
            throw new ExpectationFailedException('Mocked method does not exist.');
        }
        if (\count($this->invocation->getParameters()) < \count($this->parameters)) {
            $message = 'Parameter count for invocation %s is too low.';
            if (\count($this->parameters) === 1 &&
                \get_class($this->parameters[0]) === IsAnything::class) {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }
            throw new ExpectationFailedException(
                \sprintf($message, $this->invocation->toString())
            );
        }
        foreach ($this->parameters as $i => $parameter) {
            $parameter->evaluate(
                $this->invocation->getParameters()[$i],
                \sprintf(
                    'Parameter %s for invocation %s does not match expected ' .
                    'value.',
                    $i,
                    $this->invocation->toString()
                )
            );
        }
        return true;
    }
    private function guardAgainstDuplicateEvaluationOfParameterConstraints()
    {
        if ($this->parameterVerificationResult instanceof \Exception) {
            throw $this->parameterVerificationResult;
        }
        return (bool) $this->parameterVerificationResult;
    }
}