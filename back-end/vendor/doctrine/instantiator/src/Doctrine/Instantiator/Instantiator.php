<?php
namespace Doctrine\Instantiator;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Exception;
use ReflectionClass;
final class Instantiator implements InstantiatorInterface
{
    const SERIALIZATION_FORMAT_USE_UNSERIALIZER   = 'C';
    const SERIALIZATION_FORMAT_AVOID_UNSERIALIZER = 'O';
    private static $cachedInstantiators = [];
    private static $cachedCloneables = [];
    public function instantiate($className)
    {
        if (isset(self::$cachedCloneables[$className])) {
            return clone self::$cachedCloneables[$className];
        }
        if (isset(self::$cachedInstantiators[$className])) {
            $factory = self::$cachedInstantiators[$className];
            return $factory();
        }
        return $this->buildAndCacheFromFactory($className);
    }
    private function buildAndCacheFromFactory(string $className)
    {
        $factory  = self::$cachedInstantiators[$className] = $this->buildFactory($className);
        $instance = $factory();
        if ($this->isSafeToClone(new ReflectionClass($instance))) {
            self::$cachedCloneables[$className] = clone $instance;
        }
        return $instance;
    }
    private function buildFactory(string $className) : callable
    {
        $reflectionClass = $this->getReflectionClass($className);
        if ($this->isInstantiableViaReflection($reflectionClass)) {
            return [$reflectionClass, 'newInstanceWithoutConstructor'];
        }
        $serializedString = sprintf(
            '%s:%d:"%s":0:{}',
            self::SERIALIZATION_FORMAT_AVOID_UNSERIALIZER,
            strlen($className),
            $className
        );
        $this->checkIfUnSerializationIsSupported($reflectionClass, $serializedString);
        return function () use ($serializedString) {
            return unserialize($serializedString);
        };
    }
    private function getReflectionClass($className) : ReflectionClass
    {
        if (! class_exists($className)) {
            throw InvalidArgumentException::fromNonExistingClass($className);
        }
        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract()) {
            throw InvalidArgumentException::fromAbstractClass($reflection);
        }
        return $reflection;
    }
    private function checkIfUnSerializationIsSupported(ReflectionClass $reflectionClass, $serializedString) : void
    {
        set_error_handler(function ($code, $message, $file, $line) use ($reflectionClass, & $error) : void {
            $error = UnexpectedValueException::fromUncleanUnSerialization(
                $reflectionClass,
                $message,
                $code,
                $file,
                $line
            );
        });
        $this->attemptInstantiationViaUnSerialization($reflectionClass, $serializedString);
        restore_error_handler();
        if ($error) {
            throw $error;
        }
    }
    private function attemptInstantiationViaUnSerialization(ReflectionClass $reflectionClass, $serializedString) : void
    {
        try {
            unserialize($serializedString);
        } catch (Exception $exception) {
            restore_error_handler();
            throw UnexpectedValueException::fromSerializationTriggeredException($reflectionClass, $exception);
        }
    }
    private function isInstantiableViaReflection(ReflectionClass $reflectionClass) : bool
    {
        return ! ($this->hasInternalAncestors($reflectionClass) && $reflectionClass->isFinal());
    }
    private function hasInternalAncestors(ReflectionClass $reflectionClass) : bool
    {
        do {
            if ($reflectionClass->isInternal()) {
                return true;
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());
        return false;
    }
    private function isSafeToClone(ReflectionClass $reflection) : bool
    {
        return $reflection->isCloneable() && ! $reflection->hasMethod('__clone');
    }
}
