<?php
namespace Mockery\Exception;
use Mockery;
class NoMatchingExpectationException extends Mockery\Exception
{
    protected $method = null;
    protected $actual = array();
    protected $mockObject = null;
    public function setMock(Mockery\MockInterface $mock)
    {
        $this->mockObject = $mock;
        return $this;
    }
    public function setMethodName($name)
    {
        $this->method = $name;
        return $this;
    }
    public function setActualArguments($count)
    {
        $this->actual = $count;
        return $this;
    }
    public function getMock()
    {
        return $this->mockObject;
    }
    public function getMethodName()
    {
        return $this->method;
    }
    public function getActualArguments()
    {
        return $this->actual;
    }
    public function getMockName()
    {
        return $this->getMock()->mockery_getName();
    }
}
