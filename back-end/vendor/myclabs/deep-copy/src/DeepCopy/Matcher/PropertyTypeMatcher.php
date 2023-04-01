<?php
namespace DeepCopy\Matcher;
use DeepCopy\Reflection\ReflectionHelper;
use ReflectionException;
class PropertyTypeMatcher implements Matcher
{
    private $propertyType;
    public function __construct($propertyType)
    {
        $this->propertyType = $propertyType;
    }
    public function matches($object, $property)
    {
        try {
            $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        } catch (ReflectionException $exception) {
            return false;
        }
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object) instanceof $this->propertyType;
    }
}
