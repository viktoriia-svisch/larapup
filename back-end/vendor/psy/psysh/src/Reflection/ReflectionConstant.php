<?php
namespace Psy\Reflection;
class ReflectionConstant extends ReflectionClassConstant
{
    public function __construct($class, $name)
    {
        @\trigger_error('ReflectionConstant is now ReflectionClassConstant', E_USER_DEPRECATED);
        parent::__construct($class, $name);
    }
}
