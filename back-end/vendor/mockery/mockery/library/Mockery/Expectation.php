<?php
namespace Mockery;
use Closure;
use Mockery\Matcher\NoArgs;
use Mockery\Matcher\AnyArgs;
use Mockery\Matcher\AndAnyOtherArgs;
use Mockery\Matcher\ArgumentListMatcher;
use Mockery\Matcher\MultiArgumentClosure;
class Expectation implements ExpectationInterface
{
    protected $_mock = null;
    protected $_name = null;
    protected $_because = null;
    protected $_expectedArgs = array();
    protected $_countValidators = array();
    protected $_countValidatorClass = 'Mockery\CountValidator\Exact';
    protected $_actualCount = 0;
    protected $_returnValue = null;
    protected $_returnQueue = array();
    protected $_closureQueue = array();
    protected $_setQueue = array();
    protected $_orderNumber = null;
    protected $_globalOrderNumber = null;
    protected $_throw = false;
    protected $_globally = false;
    protected $_passthru = false;
    public function __construct(\Mockery\MockInterface $mock, $name)
    {
        $this->_mock = $mock;
        $this->_name = $name;
        $this->withAnyArgs();
    }
    public function __toString()
    {
        return \Mockery::formatArgs($this->_name, $this->_expectedArgs);
    }
    public function verifyCall(array $args)
    {
        $this->validateOrder();
        $this->_actualCount++;
        if (true === $this->_passthru) {
            return $this->_mock->mockery_callSubjectMethod($this->_name, $args);
        }
        $return = $this->_getReturnValue($args);
        $this->throwAsNecessary($return);
        $this->_setValues();
        return $return;
    }
    private function throwAsNecessary($return)
    {
        if (!$this->_throw) {
            return;
        }
        $type = version_compare(PHP_VERSION, '7.0.0') >= 0
            ? "\Throwable"
            : "\Exception";
        if ($return instanceof $type) {
            throw $return;
        }
        return;
    }
    protected function _setValues()
    {
        $mockClass = get_class($this->_mock);
        $container = $this->_mock->mockery_getContainer();
        $mocks = $container->getMocks();
        foreach ($this->_setQueue as $name => &$values) {
            if (count($values) > 0) {
                $value = array_shift($values);
                foreach ($mocks as $mock) {
                    if (is_a($mock, $mockClass)) {
                        $mock->{$name} = $value;
                    }
                }
            }
        }
    }
    protected function _getReturnValue(array $args)
    {
        if (count($this->_closureQueue) > 1) {
            return call_user_func_array(array_shift($this->_closureQueue), $args);
        } elseif (count($this->_closureQueue) > 0) {
            return call_user_func_array(current($this->_closureQueue), $args);
        } elseif (count($this->_returnQueue) > 1) {
            return array_shift($this->_returnQueue);
        } elseif (count($this->_returnQueue) > 0) {
            return current($this->_returnQueue);
        }
        return $this->_mock->mockery_returnValueForMethod($this->_name);
    }
    public function isEligible()
    {
        foreach ($this->_countValidators as $validator) {
            if (!$validator->isEligible($this->_actualCount)) {
                return false;
            }
        }
        return true;
    }
    public function isCallCountConstrained()
    {
        return (count($this->_countValidators) > 0);
    }
    public function validateOrder()
    {
        if ($this->_orderNumber) {
            $this->_mock->mockery_validateOrder((string) $this, $this->_orderNumber, $this->_mock);
        }
        if ($this->_globalOrderNumber) {
            $this->_mock->mockery_getContainer()
                ->mockery_validateOrder((string) $this, $this->_globalOrderNumber, $this->_mock);
        }
    }
    public function verify()
    {
        foreach ($this->_countValidators as $validator) {
            $validator->validate($this->_actualCount);
        }
    }
    private function isArgumentListMatcher()
    {
        return (count($this->_expectedArgs) === 1 && ($this->_expectedArgs[0] instanceof ArgumentListMatcher));
    }
    private function isAndAnyOtherArgumentsMatcher($expectedArg)
    {
        return $expectedArg instanceof AndAnyOtherArgs;
    }
    public function matchArgs(array $args)
    {
        if ($this->isArgumentListMatcher()) {
            return $this->_matchArg($this->_expectedArgs[0], $args);
        }
        $argCount = count($args);
        if ($argCount !== count((array) $this->_expectedArgs)) {
            $lastExpectedArgument = end($this->_expectedArgs);
            reset($this->_expectedArgs);
            if ($this->isAndAnyOtherArgumentsMatcher($lastExpectedArgument)) {
                $argCountToSkipMatching = $argCount - count($this->_expectedArgs);
                $args = array_slice($args, 0, $argCountToSkipMatching);
                return $this->_matchArgs($args);
            }
            return false;
        }
        return $this->_matchArgs($args);
    }
    protected function _matchArgs($args)
    {
        $argCount = count($args);
        for ($i=0; $i<$argCount; $i++) {
            $param =& $args[$i];
            if (!$this->_matchArg($this->_expectedArgs[$i], $param)) {
                return false;
            }
        }
        return true;
    }
    protected function _matchArg($expected, &$actual)
    {
        if ($expected === $actual) {
            return true;
        }
        if (!is_object($expected) && !is_object($actual) && $expected == $actual) {
            return true;
        }
        if (is_string($expected) && is_object($actual)) {
            $result = $actual instanceof $expected;
            if ($result) {
                return true;
            }
        }
        if ($expected instanceof \Mockery\Matcher\MatcherAbstract) {
            return $expected->match($actual);
        }
        if ($expected instanceof \Hamcrest\Matcher || $expected instanceof \Hamcrest_Matcher) {
            return $expected->matches($actual);
        }
        return false;
    }
    public function with(...$args)
    {
        return $this->withArgs($args);
    }
    private function withArgsInArray(array $arguments)
    {
        if (empty($arguments)) {
            return $this->withNoArgs();
        }
        $this->_expectedArgs = $arguments;
        return $this;
    }
    private function withArgsMatchedByClosure(Closure $closure)
    {
        $this->_expectedArgs = [new MultiArgumentClosure($closure)];
        return $this;
    }
    public function withArgs($argsOrClosure)
    {
        if (is_array($argsOrClosure)) {
            $this->withArgsInArray($argsOrClosure);
        } elseif ($argsOrClosure instanceof Closure) {
            $this->withArgsMatchedByClosure($argsOrClosure);
        } else {
            throw new \InvalidArgumentException(sprintf('Call to %s with an invalid argument (%s), only array and '.
                'closure are allowed', __METHOD__, $argsOrClosure));
        }
        return $this;
    }
    public function withNoArgs()
    {
        $this->_expectedArgs = [new NoArgs()];
        return $this;
    }
    public function withAnyArgs()
    {
        $this->_expectedArgs = [new AnyArgs()];
        return $this;
    }
    public function andReturn(...$args)
    {
        $this->_returnQueue = $args;
        return $this;
    }
    public function andReturns(...$args)
    {
        return call_user_func_array([$this, 'andReturn'], $args);
    }
    public function andReturnSelf()
    {
        return $this->andReturn($this->_mock);
    }
    public function andReturnValues(array $values)
    {
        call_user_func_array(array($this, 'andReturn'), $values);
        return $this;
    }
    public function andReturnUsing(...$args)
    {
        $this->_closureQueue = $args;
        return $this;
    }
    public function andReturnUndefined()
    {
        $this->andReturn(new \Mockery\Undefined);
        return $this;
    }
    public function andReturnNull()
    {
        return $this->andReturn(null);
    }
    public function andReturnFalse()
    {
        return $this->andReturn(false);
    }
    public function andReturnTrue()
    {
        return $this->andReturn(true);
    }
    public function andThrow($exception, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->_throw = true;
        if (is_object($exception)) {
            $this->andReturn($exception);
        } else {
            $this->andReturn(new $exception($message, $code, $previous));
        }
        return $this;
    }
    public function andThrows($exception, $message = '', $code = 0, \Exception $previous = null)
    {
        return $this->andThrow($exception, $message, $code, $previous);
    }
    public function andThrowExceptions(array $exceptions)
    {
        $this->_throw = true;
        foreach ($exceptions as $exception) {
            if (!is_object($exception)) {
                throw new Exception('You must pass an array of exception objects to andThrowExceptions');
            }
        }
        return $this->andReturnValues($exceptions);
    }
    public function andSet($name, ...$values)
    {
        $this->_setQueue[$name] = $values;
        return $this;
    }
    public function set($name, $value)
    {
        return call_user_func_array(array($this, 'andSet'), func_get_args());
    }
    public function zeroOrMoreTimes()
    {
        $this->atLeast()->never();
    }
    public function times($limit = null)
    {
        if (is_null($limit)) {
            return $this;
        }
        if (!is_int($limit)) {
            throw new \InvalidArgumentException('The passed Times limit should be an integer value');
        }
        $this->_countValidators[$this->_countValidatorClass] = new $this->_countValidatorClass($this, $limit);
        $this->_countValidatorClass = 'Mockery\CountValidator\Exact';
        return $this;
    }
    public function never()
    {
        return $this->times(0);
    }
    public function once()
    {
        return $this->times(1);
    }
    public function twice()
    {
        return $this->times(2);
    }
    public function atLeast()
    {
        $this->_countValidatorClass = 'Mockery\CountValidator\AtLeast';
        return $this;
    }
    public function atMost()
    {
        $this->_countValidatorClass = 'Mockery\CountValidator\AtMost';
        return $this;
    }
    public function between($minimum, $maximum)
    {
        return $this->atLeast()->times($minimum)->atMost()->times($maximum);
    }
    public function because($message)
    {
        $this->_because = $message;
        return $this;
    }
    public function ordered($group = null)
    {
        if ($this->_globally) {
            $this->_globalOrderNumber = $this->_defineOrdered($group, $this->_mock->mockery_getContainer());
        } else {
            $this->_orderNumber = $this->_defineOrdered($group, $this->_mock);
        }
        $this->_globally = false;
        return $this;
    }
    public function globally()
    {
        $this->_globally = true;
        return $this;
    }
    protected function _defineOrdered($group, $ordering)
    {
        $groups = $ordering->mockery_getGroups();
        if (is_null($group)) {
            $result = $ordering->mockery_allocateOrder();
        } elseif (isset($groups[$group])) {
            $result = $groups[$group];
        } else {
            $result = $ordering->mockery_allocateOrder();
            $ordering->mockery_setGroup($group, $result);
        }
        return $result;
    }
    public function getOrderNumber()
    {
        return $this->_orderNumber;
    }
    public function byDefault()
    {
        $director = $this->_mock->mockery_getExpectationsFor($this->_name);
        if (!empty($director)) {
            $director->makeExpectationDefault($this);
        }
        return $this;
    }
    public function getMock()
    {
        return $this->_mock;
    }
    public function passthru()
    {
        if ($this->_mock instanceof Mock) {
            throw new Exception(
                'Mock Objects not created from a loaded/existing class are '
                . 'incapable of passing method calls through to a parent class'
            );
        }
        $this->_passthru = true;
        return $this;
    }
    public function __clone()
    {
        $newValidators = array();
        $countValidators = $this->_countValidators;
        foreach ($countValidators as $validator) {
            $newValidators[] = clone $validator;
        }
        $this->_countValidators = $newValidators;
    }
    public function getName()
    {
        return $this->_name;
    }
    public function getExceptionMessage()
    {
        return $this->_because;
    }
}
