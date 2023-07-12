<?php
namespace Prophecy\Exception\Doubler;
class ClassNotFoundException extends DoubleException
{
    private $classname;
    public function __construct($message, $classname)
    {
        parent::__construct($message);
        $this->classname = $classname;
    }
    public function getClassname()
    {
        return $this->classname;
    }
}
