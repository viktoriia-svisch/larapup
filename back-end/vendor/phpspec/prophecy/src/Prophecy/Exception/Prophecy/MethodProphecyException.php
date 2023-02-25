<?php
namespace Prophecy\Exception\Prophecy;
use Prophecy\Prophecy\MethodProphecy;
class MethodProphecyException extends ObjectProphecyException
{
    private $methodProphecy;
    public function __construct($message, MethodProphecy $methodProphecy)
    {
        parent::__construct($message, $methodProphecy->getObjectProphecy());
        $this->methodProphecy = $methodProphecy;
    }
    public function getMethodProphecy()
    {
        return $this->methodProphecy;
    }
}
