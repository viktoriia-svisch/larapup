<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Generator\MockConfiguration;
class RemoveUnserializeForInternalSerializableClassesPass
{
    const DUMMY_METHOD_DEFINITION = 'public function unserialize($string) {} ';
    public function apply($code, MockConfiguration $config)
    {
        $target = $config->getTargetClass();
        if (!$target) {
            return $code;
        }
        if (!$target->hasInternalAncestor() || !$target->implementsInterface("Serializable")) {
            return $code;
        }
        $code = $this->appendToClass($code, self::DUMMY_METHOD_DEFINITION);
        return $code;
    }
    protected function appendToClass($class, $code)
    {
        $lastBrace = strrpos($class, "}");
        $class = substr($class, 0, $lastBrace) . $code . "\n    }\n";
        return $class;
    }
}
