<?php
namespace Symfony\Component\VarDumper\Cloner;
class Stub
{
    const TYPE_REF = 1;
    const TYPE_STRING = 2;
    const TYPE_ARRAY = 3;
    const TYPE_OBJECT = 4;
    const TYPE_RESOURCE = 5;
    const STRING_BINARY = 1;
    const STRING_UTF8 = 2;
    const ARRAY_ASSOC = 1;
    const ARRAY_INDEXED = 2;
    public $type = self::TYPE_REF;
    public $class = '';
    public $value;
    public $cut = 0;
    public $handle = 0;
    public $refCount = 0;
    public $position = 0;
    public $attr = [];
    private static $defaultProperties = [];
    public function __sleep()
    {
        $properties = [];
        if (!isset(self::$defaultProperties[$c = \get_class($this)])) {
            self::$defaultProperties[$c] = get_class_vars($c);
            foreach ((new \ReflectionClass($c))->getStaticProperties() as $k => $v) {
                unset(self::$defaultProperties[$c][$k]);
            }
        }
        foreach (self::$defaultProperties[$c] as $k => $v) {
            if ($this->$k !== $v) {
                $properties[] = $k;
            }
        }
        return $properties;
    }
}
