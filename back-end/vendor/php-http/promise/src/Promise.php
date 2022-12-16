<?php
namespace Http\Promise;
interface Promise
{
    const PENDING = 'pending';
    const FULFILLED = 'fulfilled';
    const REJECTED = 'rejected';
    public function then(callable $onFulfilled = null, callable $onRejected = null);
    public function getState();
    public function wait($unwrap = true);
}
