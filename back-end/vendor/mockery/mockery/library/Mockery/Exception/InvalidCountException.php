<?php
namespace Mockery\Exception;
use Mockery;
use Mockery\Exception\RuntimeException;
class InvalidCountException extends Mockery\CountValidator\Exception
{
    protected $method = null;
    protected $expected = 0;
    protected $expectedComparative = '<=';
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
    public function setActualCount($count)
    {
        $this->actual = $count;
        return $this;
    }
    public function setExpectedCount($count)
    {
        $this->expected = $count;
        return $this;
    }
    public function setExpectedCountComparative($comp)
    {
        if (!in_array($comp, array('=', '>', '<', '>=', '<='))) {
            throw new RuntimeException(
                'Illegal comparative for expected call counts set: ' . $comp
            );
        }
        $this->expectedComparative = $comp;
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
    public function getActualCount()
    {
        return $this->actual;
    }
    public function getExpectedCount()
    {
        return $this->expected;
    }
    public function getMockName()
    {
        return $this->getMock()->mockery_getName();
    }
    public function getExpectedCountComparative()
    {
        return $this->expectedComparative;
    }
}
