<?php
namespace Mockery;
use Closure;
use ReflectionClass;
use UnexpectedValueException;
use InvalidArgumentException;
final class Instantiator
{
    const SERIALIZATION_FORMAT_USE_UNSERIALIZER   = 'C';
    const SERIALIZATION_FORMAT_AVOID_UNSERIALIZER = 'O';
    public function instantiate($className)
    {
        $factory    = $this->buildFactory($className);
        $instance   = $factory();
        return $instance;
    }
    public function buildFactory($className)
    {
        $reflectionClass = $this->getReflectionClass($className);
        if ($this->isInstantiableViaReflection($reflectionClass)) {
            return function () use ($reflectionClass) {
                return $reflectionClass->newInstanceWithoutConstructor();
            };
        }
        $serializedString = sprintf(
            '%s:%d:"%s":0:{}',
            $this->getSerializationFormat($reflectionClass),
            strlen($className),
            $className
        );
        $this->attemptInstantiationViaUnSerialization($reflectionClass, $serializedString);
        return function () use ($serializedString) {
            return unserialize($serializedString);
        };
    }
    private function getReflectionClass($className)
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException("Class:$className does not exist");
        }
        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException("Class:$className is an abstract class");
        }
        return $reflection;
    }
    private function attemptInstantiationViaUnSerialization(ReflectionClass $reflectionClass, $serializedString)
    {
        set_error_handler(function ($code, $message, $file, $line) use ($reflectionClass, & $error) {
            $msg = sprintf(
                'Could not produce an instance of "%s" via un-serialization, since an error was triggered in file "%s" at line "%d"',
                $reflectionClass->getName(),
                $file,
                $line
            );
            $error = new UnexpectedValueException($msg, 0, new \Exception($message, $code));
        });
        try {
            unserialize($serializedString);
        } catch (\Exception $exception) {
            restore_error_handler();
            throw new UnexpectedValueException("An exception was raised while trying to instantiate an instance of \"{$reflectionClass->getName()}\" via un-serialization", 0, $exception);
        }
        restore_error_handler();
        if ($error) {
            throw $error;
        }
    }
    private function isInstantiableViaReflection(ReflectionClass $reflectionClass)
    {
        return ! ($reflectionClass->isInternal() && $reflectionClass->isFinal());
    }
    private function hasInternalAncestors(ReflectionClass $reflectionClass)
    {
        do {
            if ($reflectionClass->isInternal()) {
                return true;
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());
        return false;
    }
    private function getSerializationFormat(ReflectionClass $reflectionClass)
    {
        if ($this->isPhpVersionWithBrokenSerializationFormat()
            && $reflectionClass->implementsInterface('Serializable')
        ) {
            return self::SERIALIZATION_FORMAT_USE_UNSERIALIZER;
        }
        return self::SERIALIZATION_FORMAT_AVOID_UNSERIALIZER;
    }
    private function isPhpVersionWithBrokenSerializationFormat()
    {
        return PHP_VERSION_ID === 50429 || PHP_VERSION_ID === 50513;
    }
}
