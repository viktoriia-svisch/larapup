<?php
namespace phpDocumentor\Reflection\Types;
use phpDocumentor\Reflection\Type;
final class Nullable implements Type
{
    private $realType;
    public function __construct(Type $realType)
    {
        $this->realType = $realType;
    }
    public function getActualType()
    {
        return $this->realType;
    }
    public function __toString()
    {
        return '?' . $this->realType->__toString();
    }
}
