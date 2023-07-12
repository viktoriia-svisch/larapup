<?php
namespace Psy;
class Sudo
{
    public static function fetchProperty($object, $property)
    {
        $refl = new \ReflectionObject($object);
        $prop = $refl->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }
    public static function assignProperty($object, $property, $value)
    {
        $refl = new \ReflectionObject($object);
        $prop = $refl->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
        return $value;
    }
    public static function callMethod($object, $method, $args = null)
    {
        $args   = \func_get_args();
        $object = \array_shift($args);
        $method = \array_shift($args);
        $refl = new \ReflectionObject($object);
        $reflMethod = $refl->getMethod($method);
        $reflMethod->setAccessible(true);
        return $reflMethod->invokeArgs($object, $args);
    }
    public static function fetchStaticProperty($class, $property)
    {
        $refl = new \ReflectionClass($class);
        $prop = $refl->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue();
    }
    public static function assignStaticProperty($class, $property, $value)
    {
        $refl = new \ReflectionClass($class);
        $prop = $refl->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($value);
        return $value;
    }
    public static function callStatic($class, $method, $args = null)
    {
        $args   = \func_get_args();
        $class  = \array_shift($args);
        $method = \array_shift($args);
        $refl = new \ReflectionClass($class);
        $reflMethod = $refl->getMethod($method);
        $reflMethod->setAccessible(true);
        return $reflMethod->invokeArgs(null, $args);
    }
    public static function fetchClassConst($class, $const)
    {
        $refl = new \ReflectionClass($class);
        return $refl->getConstant($const);
    }
}
