<?php
declare(strict_types=1);
namespace SebastianBergmann\GlobalState;
use ReflectionClass;
class Blacklist
{
    private $globalVariables = [];
    private $classes = [];
    private $classNamePrefixes = [];
    private $parentClasses = [];
    private $interfaces = [];
    private $staticAttributes = [];
    public function addGlobalVariable(string $variableName)
    {
        $this->globalVariables[$variableName] = true;
    }
    public function addClass(string $className)
    {
        $this->classes[] = $className;
    }
    public function addSubclassesOf(string $className)
    {
        $this->parentClasses[] = $className;
    }
    public function addImplementorsOf(string $interfaceName)
    {
        $this->interfaces[] = $interfaceName;
    }
    public function addClassNamePrefix(string $classNamePrefix)
    {
        $this->classNamePrefixes[] = $classNamePrefix;
    }
    public function addStaticAttribute(string $className, string $attributeName)
    {
        if (!isset($this->staticAttributes[$className])) {
            $this->staticAttributes[$className] = [];
        }
        $this->staticAttributes[$className][$attributeName] = true;
    }
    public function isGlobalVariableBlacklisted(string $variableName): bool
    {
        return isset($this->globalVariables[$variableName]);
    }
    public function isStaticAttributeBlacklisted(string $className, string $attributeName): bool
    {
        if (\in_array($className, $this->classes)) {
            return true;
        }
        foreach ($this->classNamePrefixes as $prefix) {
            if (\strpos($className, $prefix) === 0) {
                return true;
            }
        }
        $class = new ReflectionClass($className);
        foreach ($this->parentClasses as $type) {
            if ($class->isSubclassOf($type)) {
                return true;
            }
        }
        foreach ($this->interfaces as $type) {
            if ($class->implementsInterface($type)) {
                return true;
            }
        }
        if (isset($this->staticAttributes[$className][$attributeName])) {
            return true;
        }
        return false;
    }
}
