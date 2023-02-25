<?php
namespace Psy\Reflection;
class ReflectionClassConstant implements \Reflector
{
    public $class;
    public $name;
    private $value;
    public function __construct($class, $name)
    {
        if (!$class instanceof \ReflectionClass) {
            $class = new \ReflectionClass($class);
        }
        $this->class = $class;
        $this->name  = $name;
        $constants = $class->getConstants();
        if (!\array_key_exists($name, $constants)) {
            throw new \InvalidArgumentException('Unknown constant: ' . $name);
        }
        $this->value = $constants[$name];
    }
    public static function export($class, $name, $return = false)
    {
        $refl = new self($class, $name);
        $value = $refl->getValue();
        $str = \sprintf('Constant [ public %s %s ] { %s }', \gettype($value), $refl->getName(), $value);
        if ($return) {
            return $str;
        }
        echo $str . "\n";
    }
    public function getDeclaringClass()
    {
        $parent = $this->class;
        do {
            $class  = $parent;
            $parent = $class->getParentClass();
        } while ($parent && $parent->hasConstant($this->name) && $parent->getConstant($this->name) === $this->value);
        return $class;
    }
    public function getDocComment()
    {
        return false;
    }
    public function getModifiers()
    {
        return \ReflectionMethod::IS_PUBLIC;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function isPrivate()
    {
        return false;
    }
    public function isProtected()
    {
        return false;
    }
    public function isPublic()
    {
        return true;
    }
    public function __toString()
    {
        return $this->getName();
    }
    public function getFileName()
    {
        return;
    }
    public function getStartLine()
    {
        throw new \RuntimeException('Not yet implemented because it\'s unclear what I should do here :)');
    }
    public function getEndLine()
    {
        return $this->getStartLine();
    }
    public static function create($class, $name)
    {
        if (\class_exists('\\ReflectionClassConstant')) {
            return new \ReflectionClassConstant($class, $name);
        }
        return new self($class, $name);
    }
}
