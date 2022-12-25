<?php
namespace Http\Promise;
final class FulfilledPromise implements Promise
{
    private $result;
    public function __construct($result)
    {
        $this->result = $result;
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }
        try {
            return new self($onFulfilled($this->result));
        } catch (\Exception $e) {
            return new RejectedPromise($e);
        }
    }
    public function getState()
    {
        return Promise::FULFILLED;
    }
    public function wait($unwrap = true)
    {
        if ($unwrap) {
            return $this->result;
        }
    }
}
