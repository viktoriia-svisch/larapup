<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Generator\MockConfiguration;
class ClassPass implements Pass
{
    public function apply($code, MockConfiguration $config)
    {
        $target = $config->getTargetClass();
        if (!$target) {
            return $code;
        }
        if ($target->isFinal()) {
            return $code;
        }
        $className = ltrim($target->getName(), "\\");
        if (defined('HHVM_VERSION') && preg_match('/^HH\\\\/', $className)) {
            return $code;
        }
        if (!class_exists($className)) {
            \Mockery::declareClass($className);
        }
        $code = str_replace(
            "implements MockInterface",
            "extends \\" . $className . " implements MockInterface",
            $code
        );
        return $code;
    }
}
