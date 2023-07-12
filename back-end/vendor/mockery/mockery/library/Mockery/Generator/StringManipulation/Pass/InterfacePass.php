<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Generator\MockConfiguration;
class InterfacePass implements Pass
{
    public function apply($code, MockConfiguration $config)
    {
        foreach ($config->getTargetInterfaces() as $i) {
            $name = ltrim($i->getName(), "\\");
            if (!interface_exists($name)) {
                \Mockery::declareInterface($name);
            }
        }
        $interfaces = array_reduce((array) $config->getTargetInterfaces(), function ($code, $i) {
            return $code . ", \\" . ltrim($i->getName(), "\\");
        }, "");
        $code = str_replace(
            "implements MockInterface",
            "implements MockInterface" . $interfaces,
            $code
        );
        return $code;
    }
}
