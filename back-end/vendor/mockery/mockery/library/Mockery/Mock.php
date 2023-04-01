<?php
namespace Mockery;
use Mockery\HigherOrderMessage;
use Mockery\MockInterface;
use Mockery\ExpectsHigherOrderMessage;
use Mockery\Exception\BadMethodCallException;
class Mock implements MockInterface
{
    protected $_mockery_expectations = array();
    protected $_mockery_expectations_count = 0;
    protected $_mockery_ignoreMissing = false;
    protected $_mockery_deferMissing = false;
    protected $_mockery_verified = false;
    protected $_mockery_name = null;
    protected $_mockery_allocatedOrder = 0;
    protected $_mockery_currentOrder = 0;
    protected $_mockery_groups = array();
    protected $_mockery_container = null;
    protected $_mockery_partial = null;
    protected $_mockery_disableExpectationMatching = false;
    protected $_mockery_mockableProperties = array();
    protected $_mockery_mockableMethods = array();
    protected static $_mockery_methods;
    protected $_mockery_allowMockingProtectedMethods = false;
    protected $_mockery_receivedMethodCalls;
    protected $_mockery_defaultReturnValue = null;
    protected $_mockery_thrownExceptions = [];
    public function mockery_init(\Mockery\Container $container = null, $partialObject = null)
    {
        if (is_null($container)) {
            $container = new \Mockery\Container;
        }
        $this->_mockery_container = $container;
        if (!is_null($partialObject)) {
            $this->_mockery_partial = $partialObject;
        }
        if (!\Mockery::getConfiguration()->mockingNonExistentMethodsAllowed()) {
            foreach ($this->mockery_getMethods() as $method) {
                if ($method->isPublic()) {
                    $this->_mockery_mockableMethods[] = $method->getName();
                }
            }
        }
    }
    public function shouldReceive(...$methodNames)
    {
        if (count($methodNames) === 0) {
            return new HigherOrderMessage($this, "shouldReceive");
        }
        foreach ($methodNames as $method) {
            if ("" == $method) {
                throw new \InvalidArgumentException("Received empty method name");
            }
        }
        $self = $this;
        $allowMockingProtectedMethods = $this->_mockery_allowMockingProtectedMethods;
        $lastExpectation = \Mockery::parseShouldReturnArgs(
            $this, $methodNames, function ($method) use ($self, $allowMockingProtectedMethods) {
                $rm = $self->mockery_getMethod($method);
                if ($rm) {
                    if ($rm->isPrivate()) {
                        throw new \InvalidArgumentException("$method() cannot be mocked as it is a private method");
                    }
                    if (!$allowMockingProtectedMethods && $rm->isProtected()) {
                        throw new \InvalidArgumentException("$method() cannot be mocked as it is a protected method and mocking protected methods is not enabled for the currently used mock object. Use shouldAllowMockingProtectedMethods() to enable mocking of protected methods.");
                    }
                }
                $director = $self->mockery_getExpectationsFor($method);
                if (!$director) {
                    $director = new \Mockery\ExpectationDirector($method, $self);
                    $self->mockery_setExpectationsFor($method, $director);
                }
                $expectation = new \Mockery\Expectation($self, $method);
                $director->addExpectation($expectation);
                return $expectation;
            }
        );
        return $lastExpectation;
    }
    public function allows($something = [])
    {
        if (is_string($something)) {
            return $this->shouldReceive($something);
        }
        if (empty($something)) {
            return $this->shouldReceive();
        }
        foreach ($something as $method => $returnValue) {
            $this->shouldReceive($method)->andReturn($returnValue);
        }
        return $this;
    }
    public function expects($something = null)
    {
        if (is_string($something)) {
            return $this->shouldReceive($something)->once();
        }
        return new ExpectsHigherOrderMessage($this);
    }
    public function shouldNotReceive(...$methodNames)
    {
        if (count($methodNames) === 0) {
            return new HigherOrderMessage($this, "shouldNotReceive");
        }
        $expectation = call_user_func_array(array($this, 'shouldReceive'), $methodNames);
        $expectation->never();
        return $expectation;
    }
    public function shouldAllowMockingMethod($method)
    {
        $this->_mockery_mockableMethods[] = $method;
        return $this;
    }
    public function shouldIgnoreMissing($returnValue = null)
    {
        $this->_mockery_ignoreMissing = true;
        $this->_mockery_defaultReturnValue = $returnValue;
        return $this;
    }
    public function asUndefined()
    {
        $this->_mockery_ignoreMissing = true;
        $this->_mockery_defaultReturnValue = new \Mockery\Undefined;
        return $this;
    }
    public function shouldAllowMockingProtectedMethods()
    {
        if (!\Mockery::getConfiguration()->mockingNonExistentMethodsAllowed()) {
            foreach ($this->mockery_getMethods() as $method) {
                if ($method->isProtected()) {
                    $this->_mockery_mockableMethods[] = $method->getName();
                }
            }
        }
        $this->_mockery_allowMockingProtectedMethods = true;
        return $this;
    }
    public function shouldDeferMissing()
    {
        return $this->makePartial();
    }
    public function makePartial()
    {
        $this->_mockery_deferMissing = true;
        return $this;
    }
    public function byDefault()
    {
        foreach ($this->_mockery_expectations as $director) {
            $exps = $director->getExpectations();
            foreach ($exps as $exp) {
                $exp->byDefault();
            }
        }
        return $this;
    }
    public function __call($method, array $args)
    {
        return $this->_mockery_handleMethodCall($method, $args);
    }
    public static function __callStatic($method, array $args)
    {
        return self::_mockery_handleStaticMethodCall($method, $args);
    }
    public function __toString()
    {
        return $this->__call('__toString', array());
    }
    public function mockery_verify()
    {
        if ($this->_mockery_verified) {
            return;
        }
        if (isset($this->_mockery_ignoreVerification)
            && $this->_mockery_ignoreVerification == true) {
            return;
        }
        $this->_mockery_verified = true;
        foreach ($this->_mockery_expectations as $director) {
            $director->verify();
        }
    }
    public function mockery_thrownExceptions()
    {
        return $this->_mockery_thrownExceptions;
    }
    public function mockery_teardown()
    {
    }
    public function mockery_allocateOrder()
    {
        $this->_mockery_allocatedOrder += 1;
        return $this->_mockery_allocatedOrder;
    }
    public function mockery_setGroup($group, $order)
    {
        $this->_mockery_groups[$group] = $order;
    }
    public function mockery_getGroups()
    {
        return $this->_mockery_groups;
    }
    public function mockery_setCurrentOrder($order)
    {
        $this->_mockery_currentOrder = $order;
        return $this->_mockery_currentOrder;
    }
    public function mockery_getCurrentOrder()
    {
        return $this->_mockery_currentOrder;
    }
    public function mockery_validateOrder($method, $order)
    {
        if ($order < $this->_mockery_currentOrder) {
            $exception = new \Mockery\Exception\InvalidOrderException(
                'Method ' . __CLASS__ . '::' . $method . '()'
                . ' called out of order: expected order '
                . $order . ', was ' . $this->_mockery_currentOrder
            );
            $exception->setMock($this)
                ->setMethodName($method)
                ->setExpectedOrder($order)
                ->setActualOrder($this->_mockery_currentOrder);
            throw $exception;
        }
        $this->mockery_setCurrentOrder($order);
    }
    public function mockery_getExpectationCount()
    {
        $count = $this->_mockery_expectations_count;
        foreach ($this->_mockery_expectations as $director) {
            $count += $director->getExpectationCount();
        }
        return $count;
    }
    public function mockery_setExpectationsFor($method, \Mockery\ExpectationDirector $director)
    {
        $this->_mockery_expectations[$method] = $director;
    }
    public function mockery_getExpectationsFor($method)
    {
        if (isset($this->_mockery_expectations[$method])) {
            return $this->_mockery_expectations[$method];
        }
    }
    public function mockery_findExpectation($method, array $args)
    {
        if (!isset($this->_mockery_expectations[$method])) {
            return null;
        }
        $director = $this->_mockery_expectations[$method];
        return $director->findExpectation($args);
    }
    public function mockery_getContainer()
    {
        return $this->_mockery_container;
    }
    public function mockery_getName()
    {
        return __CLASS__;
    }
    public function mockery_getMockableProperties()
    {
        return $this->_mockery_mockableProperties;
    }
    public function __isset($name)
    {
        if (false === stripos($name, '_mockery_') && method_exists(get_parent_class($this), '__isset')) {
            return parent::__isset($name);
        }
        return false;
    }
    public function mockery_getExpectations()
    {
        return $this->_mockery_expectations;
    }
    public function mockery_callSubjectMethod($name, array $args)
    {
        return call_user_func_array('parent::' . $name, $args);
    }
    public function mockery_getMockableMethods()
    {
        return $this->_mockery_mockableMethods;
    }
    public function mockery_isAnonymous()
    {
        $rfc = new \ReflectionClass($this);
        $interfaces = array_filter($rfc->getInterfaces(), function ($i) {
            return $i->getName() !== "Stringish";
        });
        $onlyImplementsMock = 1 == count($interfaces);
        return (false === $rfc->getParentClass()) && $onlyImplementsMock;
    }
    public function __wakeup()
    {
    }
    public function __destruct()
    {
    }
    public function mockery_getMethod($name)
    {
        foreach ($this->mockery_getMethods() as $method) {
            if ($method->getName() == $name) {
                return $method;
            }
        }
        return null;
    }
    public function mockery_returnValueForMethod($name)
    {
        if (version_compare(PHP_VERSION, '7.0.0-dev') < 0) {
            return;
        }
        $rm = $this->mockery_getMethod($name);
        if (!$rm || !$rm->hasReturnType()) {
            return;
        }
        $returnType = $rm->getReturnType();
        if ($returnType->allowsNull()) {
            return null;
        }
        $type = (string) $returnType;
        switch ($type) {
            case '':       return;
            case 'string': return '';
            case 'int':    return 0;
            case 'float':  return 0.0;
            case 'bool':   return false;
            case 'array':  return [];
            case 'callable':
            case 'Closure':
                return function () {
                };
            case 'Traversable':
            case 'Generator':
                $generator = eval('return function () { yield; };');
                return $generator();
            case 'self':
                return \Mockery::mock($rm->getDeclaringClass()->getName());
            case 'void':
                return null;
            case 'object':
                if (version_compare(PHP_VERSION, '7.2.0-dev') >= 0) {
                    return \Mockery::mock();
                }
            default:
                return \Mockery::mock($type);
        }
    }
    public function shouldHaveReceived($method = null, $args = null)
    {
        if ($method === null) {
            return new HigherOrderMessage($this, "shouldHaveReceived");
        }
        $expectation = new \Mockery\VerificationExpectation($this, $method);
        if (null !== $args) {
            $expectation->withArgs($args);
        }
        $expectation->atLeast()->once();
        $director = new \Mockery\VerificationDirector($this->_mockery_getReceivedMethodCalls(), $expectation);
        $this->_mockery_expectations_count++;
        $director->verify();
        return $director;
    }
    public function shouldHaveBeenCalled()
    {
        return $this->shouldHaveReceived("__invoke");
    }
    public function shouldNotHaveReceived($method = null, $args = null)
    {
        if ($method === null) {
            return new HigherOrderMessage($this, "shouldNotHaveReceived");
        }
        $expectation = new \Mockery\VerificationExpectation($this, $method);
        if (null !== $args) {
            $expectation->withArgs($args);
        }
        $expectation->never();
        $director = new \Mockery\VerificationDirector($this->_mockery_getReceivedMethodCalls(), $expectation);
        $this->_mockery_expectations_count++;
        $director->verify();
        return null;
    }
    public function shouldNotHaveBeenCalled(array $args = null)
    {
        return $this->shouldNotHaveReceived("__invoke", $args);
    }
    protected static function _mockery_handleStaticMethodCall($method, array $args)
    {
        $associatedRealObject = \Mockery::fetchMock(__CLASS__);
        try {
            return $associatedRealObject->__call($method, $args);
        } catch (BadMethodCallException $e) {
            throw new BadMethodCallException(
                'Static method ' . $associatedRealObject->mockery_getName() . '::' . $method
                . '() does not exist on this mock object',
                null,
                $e
            );
        }
    }
    protected function _mockery_getReceivedMethodCalls()
    {
        return $this->_mockery_receivedMethodCalls ?: $this->_mockery_receivedMethodCalls = new \Mockery\ReceivedMethodCalls();
    }
    protected function _mockery_constructorCalled(array $args)
    {
        if (!isset($this->_mockery_expectations['__construct']) ) {
            return;
        }
        $this->_mockery_handleMethodCall('__construct', $args);
    }
    protected function _mockery_findExpectedMethodHandler($method)
    {
        if (isset($this->_mockery_expectations[$method])) {
            return $this->_mockery_expectations[$method];
        }
        $lowerCasedMockeryExpectations = array_change_key_case($this->_mockery_expectations, CASE_LOWER);
        $lowerCasedMethod = strtolower($method);
        if (isset($lowerCasedMockeryExpectations[$lowerCasedMethod])) {
            return $lowerCasedMockeryExpectations[$lowerCasedMethod];
        }
        return null;
    }
    protected function _mockery_handleMethodCall($method, array $args)
    {
        $this->_mockery_getReceivedMethodCalls()->push(new \Mockery\MethodCall($method, $args));
        $rm = $this->mockery_getMethod($method);
        if ($rm && $rm->isProtected() && !$this->_mockery_allowMockingProtectedMethods) {
            if ($rm->isAbstract()) {
                return;
            }
            try {
                $prototype = $rm->getPrototype();
                if ($prototype->isAbstract()) {
                    return;
                }
            } catch (\ReflectionException $re) {
            }
            return call_user_func_array("parent::$method", $args);
        }
        $handler = $this->_mockery_findExpectedMethodHandler($method);
        if ($handler !== null && !$this->_mockery_disableExpectationMatching) {
            try {
                return $handler->call($args);
            } catch (\Mockery\Exception\NoMatchingExpectationException $e) {
                if (!$this->_mockery_ignoreMissing && !$this->_mockery_deferMissing) {
                    throw $e;
                }
            }
        }
        if (!is_null($this->_mockery_partial) && method_exists($this->_mockery_partial, $method)) {
            return call_user_func_array(array($this->_mockery_partial, $method), $args);
        } elseif ($this->_mockery_deferMissing && is_callable("parent::$method")
            && (!$this->hasMethodOverloadingInParentClass() || method_exists(get_parent_class($this), $method))) {
            return call_user_func_array("parent::$method", $args);
        } elseif ($method == '__toString') {
            return sprintf("%s#%s", __CLASS__, spl_object_hash($this));
        } elseif ($this->_mockery_ignoreMissing) {
            if (\Mockery::getConfiguration()->mockingNonExistentMethodsAllowed() || (method_exists($this->_mockery_partial, $method) || is_callable("parent::$method"))) {
                if ($this->_mockery_defaultReturnValue instanceof \Mockery\Undefined) {
                    return call_user_func_array(array($this->_mockery_defaultReturnValue, $method), $args);
                } elseif (null === $this->_mockery_defaultReturnValue) {
                    return $this->mockery_returnValueForMethod($method);
                }
                return $this->_mockery_defaultReturnValue;
            }
        }
        $message = 'Method ' . __CLASS__ . '::' . $method .
            '() does not exist on this mock object';
        if (!is_null($rm)) {
            $message = 'Received ' . __CLASS__ .
                '::' . $method . '(), but no expectations were specified';
        }
        $bmce = new BadMethodCallException($message);
        $this->_mockery_thrownExceptions[] = $bmce;
        throw $bmce;
    }
    protected function mockery_getMethods()
    {
        if (static::$_mockery_methods && \Mockery::getConfiguration()->reflectionCacheEnabled()) {
            return static::$_mockery_methods;
        }
        if (isset($this->_mockery_partial)) {
            $reflected = new \ReflectionObject($this->_mockery_partial);
        } else {
            $reflected = new \ReflectionClass($this);
        }
        return static::$_mockery_methods = $reflected->getMethods();
    }
    private function hasMethodOverloadingInParentClass()
    {
        return is_callable('parent::aFunctionNameThatNoOneWouldEverUseInRealLife12345');
    }
    private function getNonPublicMethods()
    {
        return array_map(
            function ($method) {
                return $method->getName();
            },
            array_filter($this->mockery_getMethods(), function ($method) {
                return !$method->isPublic();
            })
        );
    }
}
