<?php
namespace Mockery;
use Mockery\Generator\Generator;
use Mockery\Generator\MockConfigurationBuilder;
use Mockery\Loader\Loader as LoaderInterface;
class Container
{
    const BLOCKS = \Mockery::BLOCKS;
    protected $_mocks = array();
    protected $_allocatedOrder = 0;
    protected $_currentOrder = 0;
    protected $_groups = array();
    protected $_generator;
    protected $_loader;
    protected $_namedMocks = array();
    public function __construct(Generator $generator = null, LoaderInterface $loader = null)
    {
        $this->_generator = $generator ?: \Mockery::getDefaultGenerator();
        $this->_loader = $loader ?: \Mockery::getDefaultLoader();
    }
    public function mock(...$args)
    {
        $expectationClosure = null;
        $quickdefs = array();
        $constructorArgs = null;
        $blocks = array();
        $class = null;
        if (count($args) > 1) {
            $finalArg = end($args);
            reset($args);
            if (is_callable($finalArg) && is_object($finalArg)) {
                $expectationClosure = array_pop($args);
            }
        }
        $builder = new MockConfigurationBuilder();
        foreach ($args as $k => $arg) {
            if ($arg instanceof MockConfigurationBuilder) {
                $builder = $arg;
                unset($args[$k]);
            }
        }
        reset($args);
        $builder->setParameterOverrides(\Mockery::getConfiguration()->getInternalClassMethodParamMaps());
        $builder->setConstantsMap(\Mockery::getConfiguration()->getConstantsMap());
        while (count($args) > 0) {
            $arg = current($args);
            if (is_string($arg) && strpos($arg, ',') && !strpos($arg, ']')) {
                $interfaces = explode(',', str_replace(' ', '', $arg));
                $builder->addTargets($interfaces);
                array_shift($args);
                continue;
            } elseif (is_string($arg) && substr($arg, 0, 6) == 'alias:') {
                $name = array_shift($args);
                $name = str_replace('alias:', '', $name);
                $builder->addTarget('stdClass');
                $builder->setName($name);
                continue;
            } elseif (is_string($arg) && substr($arg, 0, 9) == 'overload:') {
                $name = array_shift($args);
                $name = str_replace('overload:', '', $name);
                $builder->setInstanceMock(true);
                $builder->addTarget('stdClass');
                $builder->setName($name);
                continue;
            } elseif (is_string($arg) && substr($arg, strlen($arg)-1, 1) == ']') {
                $parts = explode('[', $arg);
                if (!class_exists($parts[0], true) && !interface_exists($parts[0], true)) {
                    throw new \Mockery\Exception('Can only create a partial mock from'
                    . ' an existing class or interface');
                }
                $class = $parts[0];
                $parts[1] = str_replace(' ', '', $parts[1]);
                $partialMethods = explode(',', strtolower(rtrim($parts[1], ']')));
                $builder->addTarget($class);
                $builder->setWhiteListedMethods($partialMethods);
                array_shift($args);
                continue;
            } elseif (is_string($arg) && (class_exists($arg, true) || interface_exists($arg, true) || trait_exists($arg, true))) {
                $class = array_shift($args);
                $builder->addTarget($class);
                continue;
            } elseif (is_string($arg) && !\Mockery::getConfiguration()->mockingNonExistentMethodsAllowed() && (!class_exists($arg, true) && !interface_exists($arg, true))) {
                throw new \Mockery\Exception("Mockery can't find '$arg' so can't mock it");
            } elseif (is_string($arg)) {
                if (!$this->isValidClassName($arg)) {
                    throw new \Mockery\Exception('Class name contains invalid characters');
                }
                $class = array_shift($args);
                $builder->addTarget($class);
                continue;
            } elseif (is_object($arg)) {
                $partial = array_shift($args);
                $builder->addTarget($partial);
                continue;
            } elseif (is_array($arg) && !empty($arg) && array_keys($arg) !== range(0, count($arg) - 1)) {
                if (array_key_exists(self::BLOCKS, $arg)) {
                    $blocks = $arg[self::BLOCKS];
                }
                unset($arg[self::BLOCKS]);
                $quickdefs = array_shift($args);
                continue;
            } elseif (is_array($arg)) {
                $constructorArgs = array_shift($args);
                continue;
            }
            throw new \Mockery\Exception(
                'Unable to parse arguments sent to '
                . get_class($this) . '::mock()'
            );
        }
        $builder->addBlackListedMethods($blocks);
        if (defined('HHVM_VERSION')
            && ($class === 'Exception' || is_subclass_of($class, 'Exception'))) {
            $builder->addBlackListedMethod("setTraceOptions");
            $builder->addBlackListedMethod("getTraceOptions");
        }
        if (!is_null($constructorArgs)) {
            $builder->addBlackListedMethod("__construct"); 
        } else {
            $builder->setMockOriginalDestructor(true);
        }
        if (!empty($partialMethods) && $constructorArgs === null) {
            $constructorArgs = array();
        }
        $config = $builder->getMockConfiguration();
        $this->checkForNamedMockClashes($config);
        $def = $this->getGenerator()->generate($config);
        if (class_exists($def->getClassName(), $attemptAutoload = false)) {
            $rfc = new \ReflectionClass($def->getClassName());
            if (!$rfc->implementsInterface("Mockery\MockInterface")) {
                throw new \Mockery\Exception\RuntimeException("Could not load mock {$def->getClassName()}, class already exists");
            }
        }
        $this->getLoader()->load($def);
        $mock = $this->_getInstance($def->getClassName(), $constructorArgs);
        $mock->mockery_init($this, $config->getTargetObject());
        if (!empty($quickdefs)) {
            $mock->shouldReceive($quickdefs)->byDefault();
        }
        if (!empty($expectationClosure)) {
            $expectationClosure($mock);
        }
        $this->rememberMock($mock);
        return $mock;
    }
    public function instanceMock()
    {
    }
    public function getLoader()
    {
        return $this->_loader;
    }
    public function getGenerator()
    {
        return $this->_generator;
    }
    public function getKeyOfDemeterMockFor($method, $parent)
    {
        $keys = array_keys($this->_mocks);
        $match = preg_grep("/__demeter_" . md5($parent) . "_{$method}$/", $keys);
        if (count($match) == 1) {
            $res = array_values($match);
            if (count($res) > 0) {
                return $res[0];
            }
        }
        return null;
    }
    public function getMocks()
    {
        return $this->_mocks;
    }
    public function mockery_teardown()
    {
        try {
            $this->mockery_verify();
        } catch (\Exception $e) {
            $this->mockery_close();
            throw $e;
        }
    }
    public function mockery_verify()
    {
        foreach ($this->_mocks as $mock) {
            $mock->mockery_verify();
        }
    }
    public function mockery_thrownExceptions()
    {
        $e = [];
        foreach ($this->_mocks as $mock) {
            $e = array_merge($e, $mock->mockery_thrownExceptions());
        }
        return $e;
    }
    public function mockery_close()
    {
        foreach ($this->_mocks as $mock) {
            $mock->mockery_teardown();
        }
        $this->_mocks = array();
    }
    public function mockery_allocateOrder()
    {
        $this->_allocatedOrder += 1;
        return $this->_allocatedOrder;
    }
    public function mockery_setGroup($group, $order)
    {
        $this->_groups[$group] = $order;
    }
    public function mockery_getGroups()
    {
        return $this->_groups;
    }
    public function mockery_setCurrentOrder($order)
    {
        $this->_currentOrder = $order;
        return $this->_currentOrder;
    }
    public function mockery_getCurrentOrder()
    {
        return $this->_currentOrder;
    }
    public function mockery_validateOrder($method, $order, \Mockery\MockInterface $mock)
    {
        if ($order < $this->_currentOrder) {
            $exception = new \Mockery\Exception\InvalidOrderException(
                'Method ' . $method . ' called out of order: expected order '
                . $order . ', was ' . $this->_currentOrder
            );
            $exception->setMock($mock)
                ->setMethodName($method)
                ->setExpectedOrder($order)
                ->setActualOrder($this->_currentOrder);
            throw $exception;
        }
        $this->mockery_setCurrentOrder($order);
    }
    public function mockery_getExpectationCount()
    {
        $count = 0;
        foreach ($this->_mocks as $mock) {
            $count += $mock->mockery_getExpectationCount();
        }
        return $count;
    }
    public function rememberMock(\Mockery\MockInterface $mock)
    {
        if (!isset($this->_mocks[get_class($mock)])) {
            $this->_mocks[get_class($mock)] = $mock;
        } else {
            $this->_mocks[] = $mock;
        }
        return $mock;
    }
    public function self()
    {
        $mocks = array_values($this->_mocks);
        $index = count($mocks) - 1;
        return $mocks[$index];
    }
    public function fetchMock($reference)
    {
        if (isset($this->_mocks[$reference])) {
            return $this->_mocks[$reference];
        }
    }
    protected function _getInstance($mockName, $constructorArgs = null)
    {
        if ($constructorArgs !== null) {
            $r = new \ReflectionClass($mockName);
            return $r->newInstanceArgs($constructorArgs);
        }
        try {
            $instantiator = new Instantiator;
            $instance = $instantiator->instantiate($mockName);
        } catch (\Exception $ex) {
            $internalMockName = $mockName . '_Internal';
            if (!class_exists($internalMockName)) {
                eval("class $internalMockName extends $mockName {" .
                        'public function __construct() {}' .
                    '}');
            }
            $instance = new $internalMockName();
        }
        return $instance;
    }
    protected function checkForNamedMockClashes($config)
    {
        $name = $config->getName();
        if (!$name) {
            return;
        }
        $hash = $config->getHash();
        if (isset($this->_namedMocks[$name])) {
            if ($hash !== $this->_namedMocks[$name]) {
                throw new \Mockery\Exception(
                    "The mock named '$name' has been already defined with a different mock configuration"
                );
            }
        }
        $this->_namedMocks[$name] = $hash;
    }
    public function isValidClassName($className)
    {
        $pos = strpos($className, '\\');
        if ($pos === 0) {
            $className = substr($className, 1); 
        }
        $invalidNames = array_filter(explode('\\', $className), function ($name) {
            return !preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name);
        });
        return empty($invalidNames);
    }
}
