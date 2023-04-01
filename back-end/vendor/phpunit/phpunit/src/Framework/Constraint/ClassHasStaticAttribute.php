<?php
namespace PHPUnit\Framework\Constraint;
use ReflectionClass;
class ClassHasStaticAttribute extends ClassHasAttribute
{
    public function toString(): string
    {
        return \sprintf(
            'has static attribute "%s"',
            $this->attributeName()
        );
    }
    protected function matches($other): bool
    {
        $class = new ReflectionClass($other);
        if ($class->hasProperty($this->attributeName())) {
            $attribute = $class->getProperty($this->attributeName());
            return $attribute->isStatic();
        }
        return false;
    }
}
