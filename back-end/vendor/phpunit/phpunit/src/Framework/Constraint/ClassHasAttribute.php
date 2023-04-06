<?php
namespace PHPUnit\Framework\Constraint;
use ReflectionClass;
class ClassHasAttribute extends Constraint
{
    private $attributeName;
    public function __construct(string $attributeName)
    {
        parent::__construct();
        $this->attributeName = $attributeName;
    }
    public function toString(): string
    {
        return \sprintf(
            'has attribute "%s"',
            $this->attributeName
        );
    }
    protected function matches($other): bool
    {
        $class = new ReflectionClass($other);
        return $class->hasProperty($this->attributeName);
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%sclass "%s" %s',
            \is_object($other) ? 'object of ' : '',
            \is_object($other) ? \get_class($other) : $other,
            $this->toString()
        );
    }
    protected function attributeName(): string
    {
        return $this->attributeName;
    }
}
