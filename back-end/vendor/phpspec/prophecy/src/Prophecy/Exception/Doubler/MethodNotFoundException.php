<?php
namespace Prophecy\Exception\Doubler;
class MethodNotFoundException extends DoubleException
{
    private $classname;
    private $methodName;
    private $arguments;
    public function __construct($message, $classname, $methodName, $arguments = null)
    {
        parent::__construct($message);
        $this->classname  = $classname;
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }
    public function getClassname()
    {
        return $this->classname;
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
