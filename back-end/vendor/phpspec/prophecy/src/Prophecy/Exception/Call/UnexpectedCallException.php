<?php
namespace Prophecy\Exception\Call;
use Prophecy\Exception\Prophecy\ObjectProphecyException;
use Prophecy\Prophecy\ObjectProphecy;
class UnexpectedCallException extends ObjectProphecyException
{
    private $methodName;
    private $arguments;
    public function __construct($message, ObjectProphecy $objectProphecy,
                                $methodName, array $arguments)
    {
        parent::__construct($message, $objectProphecy);
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }
    public function getMethodName()
    {
        return $this->methodName;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
}
