<?php
namespace PHPUnit\Framework\Constraint;
use ReflectionObject;
class ObjectHasAttribute extends ClassHasAttribute
{
    protected function matches($other): bool
    {
        $object = new ReflectionObject($other);
        return $object->hasProperty($this->attributeName());
    }
}
