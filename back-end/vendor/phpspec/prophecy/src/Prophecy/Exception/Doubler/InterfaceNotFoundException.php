<?php
namespace Prophecy\Exception\Doubler;
class InterfaceNotFoundException extends ClassNotFoundException
{
    public function getInterfaceName()
    {
        return $this->getClassname();
    }
}
