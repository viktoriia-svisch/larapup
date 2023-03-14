<?php
namespace Prophecy\Exception\Prediction;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Exception\Prophecy\MethodProphecyException;
class UnexpectedCallsException extends MethodProphecyException implements PredictionException
{
    private $calls = array();
    public function __construct($message, MethodProphecy $methodProphecy, array $calls)
    {
        parent::__construct($message, $methodProphecy);
        $this->calls = $calls;
    }
    public function getCalls()
    {
        return $this->calls;
    }
}
