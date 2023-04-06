<?php
namespace Prophecy\Prediction;
use Prophecy\Call\Call;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;
interface PredictionInterface
{
    public function check(array $calls, ObjectProphecy $object, MethodProphecy $method);
}
