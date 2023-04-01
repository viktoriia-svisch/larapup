<?php
namespace Mockery;
class ExpectationDirector
{
    protected $_name = null;
    protected $_mock = null;
    protected $_expectations = array();
    protected $_expectedOrder = null;
    protected $_defaults = array();
    public function __construct($name, \Mockery\MockInterface $mock)
    {
        $this->_name = $name;
        $this->_mock = $mock;
    }
    public function addExpectation(\Mockery\Expectation $expectation)
    {
        $this->_expectations[] = $expectation;
    }
    public function call(array $args)
    {
        $expectation = $this->findExpectation($args);
        if (is_null($expectation)) {
            $exception = new \Mockery\Exception\NoMatchingExpectationException(
                'No matching handler found for '
                . $this->_mock->mockery_getName() . '::'
                . \Mockery::formatArgs($this->_name, $args)
                . '. Either the method was unexpected or its arguments matched'
                . ' no expected argument list for this method'
                . PHP_EOL . PHP_EOL
                . \Mockery::formatObjects($args)
            );
            $exception->setMock($this->_mock)
                ->setMethodName($this->_name)
                ->setActualArguments($args);
            throw $exception;
        }
        return $expectation->verifyCall($args);
    }
    public function verify()
    {
        if (!empty($this->_expectations)) {
            foreach ($this->_expectations as $exp) {
                $exp->verify();
            }
        } else {
            foreach ($this->_defaults as $exp) {
                $exp->verify();
            }
        }
    }
    public function findExpectation(array $args)
    {
        $expectation = null;
        if (!empty($this->_expectations)) {
            $expectation = $this->_findExpectationIn($this->_expectations, $args);
        }
        if ($expectation === null && !empty($this->_defaults)) {
            $expectation = $this->_findExpectationIn($this->_defaults, $args);
        }
        return $expectation;
    }
    public function makeExpectationDefault(\Mockery\Expectation $expectation)
    {
        $last = end($this->_expectations);
        if ($last === $expectation) {
            array_pop($this->_expectations);
            array_unshift($this->_defaults, $expectation);
        } else {
            throw new \Mockery\Exception(
                'Cannot turn a previously defined expectation into a default'
            );
        }
    }
    protected function _findExpectationIn(array $expectations, array $args)
    {
        foreach ($expectations as $exp) {
            if ($exp->isEligible() && $exp->matchArgs($args)) {
                return $exp;
            }
        }
        foreach ($expectations as $exp) {
            if ($exp->matchArgs($args)) {
                return $exp;
            }
        }
    }
    public function getExpectations()
    {
        return $this->_expectations;
    }
    public function getDefaultExpectations()
    {
        return $this->_defaults;
    }
    public function getExpectationCount()
    {
        return count($this->getExpectations()) ?: count($this->getDefaultExpectations());
    }
}
