<?php
namespace Psy\Reflection;
class ReflectionConstant_ implements \Reflector
{
    public $name;
    private $value;
    private static $magicConstants = [
        '__LINE__',
        '__FILE__',
        '__DIR__',
        '__FUNCTION__',
        '__CLASS__',
        '__TRAIT__',
        '__METHOD__',
        '__NAMESPACE__',
        '__COMPILER_HALT_OFFSET__',
    ];
    public function __construct($name)
    {
        $this->name = $name;
        if (!\defined($name) && !self::isMagicConstant($name)) {
            throw new \InvalidArgumentException('Unknown constant: ' . $name);
        }
        if (!self::isMagicConstant($name)) {
            $this->value = @\constant($name);
        }
    }
    public static function export($name, $return = false)
    {
        $refl = new self($name);
        $value = $refl->getValue();
        $str = \sprintf('Constant [ %s %s ] { %s }', \gettype($value), $refl->getName(), $value);
        if ($return) {
            return $str;
        }
        echo $str . "\n";
    }
    public static function isMagicConstant($name)
    {
        return \in_array($name, self::$magicConstants);
    }
    public function getDocComment()
    {
        return false;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getNamespaceName()
    {
        if (!$this->inNamespace()) {
            return '';
        }
        return \preg_replace('/\\\\[^\\\\]+$/', '', $this->name);
    }
    public function getValue()
    {
        return $this->value;
    }
    public function inNamespace()
    {
        return \strpos($this->name, '\\') !== false;
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
}
