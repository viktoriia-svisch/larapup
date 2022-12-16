<?php
namespace Psy\Util;
use Psy\Exception\RuntimeException;
use Psy\Reflection\ReflectionClassConstant;
use Psy\Reflection\ReflectionConstant_;
class Mirror
{
    const CONSTANT        = 1;
    const METHOD          = 2;
    const STATIC_PROPERTY = 4;
    const PROPERTY        = 8;
    public static function get($value, $member = null, $filter = 15)
    {
        if ($member === null && \is_string($value)) {
            if (\function_exists($value)) {
                return new \ReflectionFunction($value);
            } elseif (\defined($value) || ReflectionConstant_::isMagicConstant($value)) {
                return new ReflectionConstant_($value);
            }
        }
        $class = self::getClass($value);
        if ($member === null) {
            return $class;
        } elseif ($filter & self::CONSTANT && $class->hasConstant($member)) {
            return ReflectionClassConstant::create($value, $member);
        } elseif ($filter & self::METHOD && $class->hasMethod($member)) {
            return $class->getMethod($member);
        } elseif ($filter & self::PROPERTY && $class->hasProperty($member)) {
            return $class->getProperty($member);
        } elseif ($filter & self::STATIC_PROPERTY && $class->hasProperty($member) && $class->getProperty($member)->isStatic()) {
            return $class->getProperty($member);
        } else {
            throw new RuntimeException(\sprintf(
                'Unknown member %s on class %s',
                $member,
                \is_object($value) ? \get_class($value) : $value
            ));
        }
    }
    private static function getClass($value)
    {
        if (\is_object($value)) {
            return new \ReflectionObject($value);
        }
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('Mirror expects an object or class');
        } elseif (!\class_exists($value) && !\interface_exists($value) && !\trait_exists($value)) {
            throw new \InvalidArgumentException('Unknown class or function: ' . $value);
        }
        return new \ReflectionClass($value);
    }
}
