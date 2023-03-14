<?php
namespace Mockery;
interface ExpectationInterface
{
    public function getOrderNumber();
    public function getMock();
    public function andReturn(...$args);
    public function andReturns();
}
