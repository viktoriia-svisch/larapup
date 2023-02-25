<?php
namespace Mockery;
class CompositeExpectation implements ExpectationInterface
{
    protected $_expectations = array();
    public function add($expectation)
    {
        $this->_expectations[] = $expectation;
    }
    public function andReturn(...$args)
    {
        return $this->__call(__FUNCTION__, $args);
    }
    public function andReturns(...$args)
    {
        return call_user_func_array([$this, 'andReturn'], $args);
    }
    public function __call($method, array $args)
    {
        foreach ($this->_expectations as $expectation) {
            call_user_func_array(array($expectation, $method), $args);
        }
        return $this;
    }
    public function getOrderNumber()
    {
        reset($this->_expectations);
        $first = current($this->_expectations);
        return $first->getOrderNumber();
    }
    public function getMock()
    {
        reset($this->_expectations);
        $first = current($this->_expectations);
        return $first->getMock();
    }
    public function mock()
    {
        return $this->getMock();
    }
    public function shouldReceive(...$args)
    {
        reset($this->_expectations);
        $first = current($this->_expectations);
        return call_user_func_array(array($first->getMock(), 'shouldReceive'), $args);
    }
    public function shouldNotReceive(...$args)
    {
        reset($this->_expectations);
        $first = current($this->_expectations);
        return call_user_func_array(array($first->getMock(), 'shouldNotReceive'), $args);
    }
    public function __toString()
    {
        $return = '[';
        $parts = array();
        foreach ($this->_expectations as $exp) {
            $parts[] = (string) $exp;
        }
        $return .= implode(', ', $parts) . ']';
        return $return;
    }
}
