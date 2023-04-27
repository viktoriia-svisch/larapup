<?php
namespace Mockery;
class HigherOrderMessage
{
    private $mock;
    private $method;
    public function __construct(MockInterface $mock, $method)
    {
        $this->mock = $mock;
        $this->method = $method;
    }
    public function __call($method, $args)
    {
        if ($this->method === 'shouldNotHaveReceived') {
            return $this->mock->{$this->method}($method, $args);
        }
        $expectation = $this->mock->{$this->method}($method);
        return $expectation->withArgs($args);
    }
}
