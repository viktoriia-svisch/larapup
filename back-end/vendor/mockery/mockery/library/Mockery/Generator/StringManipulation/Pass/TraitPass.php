<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Generator\MockConfiguration;
class TraitPass implements Pass
{
    public function apply($code, MockConfiguration $config)
    {
        $traits = $config->getTargetTraits();
        if (empty($traits)) {
            return $code;
        }
        $useStatements = array_map(function ($trait) {
            return "use \\\\".ltrim($trait->getName(), "\\").";";
        }, $traits);
        $code = preg_replace(
            '/^{$/m',
            "{\n    ".implode("\n    ", $useStatements)."\n",
            $code
        );
        return $code;
    }
}
