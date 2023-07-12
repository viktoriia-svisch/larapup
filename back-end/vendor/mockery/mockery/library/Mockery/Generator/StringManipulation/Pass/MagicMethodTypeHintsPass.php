<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Generator\MockConfiguration;
use Mockery\Generator\TargetClassInterface;
use Mockery\Generator\Method;
class MagicMethodTypeHintsPass implements Pass
{
    private $mockMagicMethods = array(
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        '__debugInfo'
    );
    public function apply($code, MockConfiguration $config)
    {
        $magicMethods = $this->getMagicMethods($config->getTargetClass());
        foreach ($config->getTargetInterfaces() as $interface) {
            $magicMethods = array_merge($magicMethods, $this->getMagicMethods($interface));
        }
        foreach ($magicMethods as $method) {
            $code = $this->applyMagicTypeHints($code, $method);
        }
        return $code;
    }
    public function getMagicMethods(
        TargetClassInterface $class = null
    ) {
        if (is_null($class)) {
            return array();
        }
        return array_filter($class->getMethods(), function (Method $method) {
            return in_array($method->getName(), $this->mockMagicMethods);
        });
    }
    private function applyMagicTypeHints($code, Method $method)
    {
        if ($this->isMethodWithinCode($code, $method)) {
            $namedParameters = $this->getOriginalParameters(
                $code,
                $method
            );
            $code = preg_replace(
                $this->getDeclarationRegex($method->getName()),
                $this->getMethodDeclaration($method, $namedParameters),
                $code
            );
        }
        return $code;
    }
    private function isMethodWithinCode($code, Method $method)
    {
        return preg_match(
            $this->getDeclarationRegex($method->getName()),
            $code
        ) == 1;
    }
    private function getOriginalParameters($code, Method $method)
    {
        $matches = [];
        $parameterMatches = [];
        preg_match(
            $this->getDeclarationRegex($method->getName()),
            $code,
            $matches
        );
        if (count($matches) > 0) {
            preg_match_all(
                '/(?<=\$)(\w+)+/i',
                $matches[0],
                $parameterMatches
            );
        }
        $groupMatches = end($parameterMatches);
        $parameterNames = is_array($groupMatches) ?
            $groupMatches                         :
            array($groupMatches);
        return $parameterNames;
    }
    private function getMethodDeclaration(
        Method $method,
        array $namedParameters
    ) {
        $declaration = 'public';
        $declaration .= $method->isStatic() ? ' static' : '';
        $declaration .= ' function '.$method->getName().'(';
        foreach ($method->getParameters() as $index => $parameter) {
            $declaration .= $parameter->getTypeHintAsString().' ';
            $name = isset($namedParameters[$index]) ?
                $namedParameters[$index]            :
                $parameter->getName();
            $declaration .= '$'.$name;
            $declaration .= ',';
        }
        $declaration = rtrim($declaration, ',');
        $declaration .= ') ';
        $returnType = $method->getReturnType();
        if (!empty($returnType)) {
            $declaration .= ': '.$returnType;
        }
        return $declaration;
    }
    private function getDeclarationRegex($methodName)
    {
        return "/public\s+(?:static\s+)?function\s+$methodName\s*\(.*\)\s*(?=\{)/i";
    }
}
