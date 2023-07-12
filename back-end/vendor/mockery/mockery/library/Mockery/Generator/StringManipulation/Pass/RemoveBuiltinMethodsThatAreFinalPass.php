<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Generator\MockConfiguration;
class RemoveBuiltinMethodsThatAreFinalPass
{
    protected $methods = array(
        '__wakeup' => '/public function __wakeup\(\)\s+\{.*?\}/sm',
    );
    public function apply($code, MockConfiguration $config)
    {
        $target = $config->getTargetClass();
        if (!$target) {
            return $code;
        }
        foreach ($target->getMethods() as $method) {
            if ($method->isFinal() && isset($this->methods[$method->getName()])) {
                $code = preg_replace($this->methods[$method->getName()], '', $code);
            }
        }
        return $code;
    }
}
