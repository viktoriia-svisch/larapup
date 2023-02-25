<?php
namespace Mockery\Exception;
use Mockery;
class InvalidOrderException extends Mockery\Exception
{
    protected $method = null;
    protected $expected = 0;
    protected $actual = null;
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
    public function setActualOrder($count)
    {
        $this->actual = $count;
        return $this;
    }
    public function setExpectedOrder($count)
    {
        $this->expected = $count;
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
    public function getActualOrder()
    {
        return $this->actual;
    }
    public function getExpectedOrder()
    {
        return $this->expected;
    }
    public function getMockName()
    {
        return $this->getMock()->mockery_getName();
    }
}
