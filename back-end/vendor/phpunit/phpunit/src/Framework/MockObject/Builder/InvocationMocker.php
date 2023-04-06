<?php
namespace PHPUnit\Framework\MockObject\Builder;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Stub\MatcherCollection;
class InvocationMocker implements MethodNameMatch
{
    private $collection;
    private $matcher;
    private $configurableMethods;
    public function __construct(MatcherCollection $collection, Invocation $invocationMatcher, array $configurableMethods)
    {
        $this->collection = $collection;
        $this->matcher    = new Matcher($invocationMatcher);
        $this->collection->addMatcher($this->matcher);
        $this->configurableMethods = $configurableMethods;
    }
    public function getMatcher()
    {
        return $this->matcher;
    }
    public function id($id)
    {
        $this->collection->registerId($id, $this);
        return $this;
    }
    public function will(Stub $stub)
    {
        $this->matcher->setStub($stub);
        return $this;
    }
    public function willReturn($value, ...$nextValues)
    {
        if (\count($nextValues) === 0) {
            $stub = new Stub\ReturnStub($value);
        } else {
            $stub = new Stub\ConsecutiveCalls(
                \array_merge([$value], $nextValues)
            );
        }
        return $this->will($stub);
    }
    public function willReturnReference(&$reference)
    {
        $stub = new Stub\ReturnReference($reference);
        return $this->will($stub);
    }
    public function willReturnMap(array $valueMap)
    {
        $stub = new Stub\ReturnValueMap($valueMap);
        return $this->will($stub);
    }
    public function willReturnArgument($argumentIndex)
    {
        $stub = new Stub\ReturnArgument($argumentIndex);
        return $this->will($stub);
    }
    public function willReturnCallback($callback)
    {
        $stub = new Stub\ReturnCallback($callback);
        return $this->will($stub);
    }
    public function willReturnSelf()
    {
        $stub = new Stub\ReturnSelf;
        return $this->will($stub);
    }
    public function willReturnOnConsecutiveCalls(...$values)
    {
        $stub = new Stub\ConsecutiveCalls($values);
        return $this->will($stub);
    }
    public function willThrowException(\Exception $exception)
    {
        $stub = new Stub\Exception($exception);
        return $this->will($stub);
    }
    public function after($id)
    {
        $this->matcher->setAfterMatchBuilderId($id);
        return $this;
    }
    public function with(...$arguments)
    {
        $this->canDefineParameters();
        $this->matcher->setParametersMatcher(new Matcher\Parameters($arguments));
        return $this;
    }
    public function withConsecutive(...$arguments)
    {
        $this->canDefineParameters();
        $this->matcher->setParametersMatcher(new Matcher\ConsecutiveParameters($arguments));
        return $this;
    }
    public function withAnyParameters()
    {
        $this->canDefineParameters();
        $this->matcher->setParametersMatcher(new Matcher\AnyParameters);
        return $this;
    }
    public function method($constraint)
    {
        if ($this->matcher->hasMethodNameMatcher()) {
            throw new RuntimeException(
                'Method name matcher is already defined, cannot redefine'
            );
        }
        if (\is_string($constraint) && !\in_array(\strtolower($constraint), $this->configurableMethods, true)) {
            throw new RuntimeException(
                \sprintf(
                    'Trying to configure method "%s" which cannot be configured because it does not exist, has not been specified, is final, or is static',
                    $constraint
                )
            );
        }
        $this->matcher->setMethodNameMatcher(new Matcher\MethodName($constraint));
        return $this;
    }
    private function canDefineParameters(): void
    {
        if (!$this->matcher->hasMethodNameMatcher()) {
            throw new RuntimeException(
                'Method name matcher is not defined, cannot define parameter ' .
                'matcher without one'
            );
        }
        if ($this->matcher->hasParametersMatcher()) {
            throw new RuntimeException(
                'Parameter matcher is already defined, cannot redefine'
            );
        }
    }
}
