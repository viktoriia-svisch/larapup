<?php
namespace Prophecy\Exception\Prophecy;
use Prophecy\Prophecy\ObjectProphecy;
class ObjectProphecyException extends \RuntimeException implements ProphecyException
{
    private $objectProphecy;
    public function __construct($message, ObjectProphecy $objectProphecy)
    {
        parent::__construct($message);
        $this->objectProphecy = $objectProphecy;
    }
    public function getObjectProphecy()
    {
        return $this->objectProphecy;
    }
}
