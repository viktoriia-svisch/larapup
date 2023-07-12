<?php
namespace PHPUnit\Framework\MockObject\Invocation;
class ObjectInvocation extends StaticInvocation
{
    private $object;
    public function __construct($className, $methodName, array $parameters, $returnType, $object, $cloneObjects = false)
    {
        parent::__construct($className, $methodName, $parameters, $returnType, $cloneObjects);
        $this->object = $object;
    }
    public function getObject()
    {
        return $this->object;
    }
}
