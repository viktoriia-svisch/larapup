<?php
namespace Prophecy\Promise;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;
interface PromiseInterface
{
    public function execute(array $args, ObjectProphecy $object, MethodProphecy $method);
}
