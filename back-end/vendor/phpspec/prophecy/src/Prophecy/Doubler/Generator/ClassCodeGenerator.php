<?php
namespace Prophecy\Doubler\Generator;
class ClassCodeGenerator
{
    private $typeHintReference;
    public function __construct(TypeHintReference $typeHintReference = null)
    {
        $this->typeHintReference = $typeHintReference ?: new TypeHintReference();
    }
    public function generate($classname, Node\ClassNode $class)
    {
        $parts     = explode('\\', $classname);
        $classname = array_pop($parts);
        $namespace = implode('\\', $parts);
        $code = sprintf("class %s extends \%s implements %s {\n",
            $classname, $class->getParentClass(), implode(', ',
                array_map(function ($interface) {return '\\'.$interface;}, $class->getInterfaces())
            )
        );
        foreach ($class->getProperties() as $name => $visibility) {
            $code .= sprintf("%s \$%s;\n", $visibility, $name);
        }
        $code .= "\n";
        foreach ($class->getMethods() as $method) {
            $code .= $this->generateMethod($method)."\n";
        }
        $code .= "\n}";
        return sprintf("namespace %s {\n%s\n}", $namespace, $code);
    }
    private function generateMethod(Node\MethodNode $method)
    {
        $php = sprintf("%s %s function %s%s(%s)%s {\n",
            $method->getVisibility(),
            $method->isStatic() ? 'static' : '',
            $method->returnsReference() ? '&':'',
            $method->getName(),
            implode(', ', $this->generateArguments($method->getArguments())),
            $this->getReturnType($method)
        );
        $php .= $method->getCode()."\n";
        return $php.'}';
    }
    private function getReturnType(Node\MethodNode $method)
    {
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            if ($method->hasReturnType()) {
                return $method->hasNullableReturnType()
                    ? sprintf(': ?%s', $method->getReturnType())
                    : sprintf(': %s', $method->getReturnType());
            }
        }
        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            return $method->hasReturnType() && $method->getReturnType() !== 'void'
                ? sprintf(': %s', $method->getReturnType())
                : '';
        }
        return '';
    }
    private function generateArguments(array $arguments)
    {
        $typeHintReference = $this->typeHintReference;
        return array_map(function (Node\ArgumentNode $argument) use ($typeHintReference) {
            $php = '';
            if (version_compare(PHP_VERSION, '7.1', '>=')) {
                $php .= $argument->isNullable() ? '?' : '';
            }
            if ($hint = $argument->getTypeHint()) {
                $php .= $typeHintReference->isBuiltInParamTypeHint($hint) ? $hint : '\\'.$hint;
            }
            $php .= ' '.($argument->isPassedByReference() ? '&' : '');
            $php .= $argument->isVariadic() ? '...' : '';
            $php .= '$'.$argument->getName();
            if ($argument->isOptional() && !$argument->isVariadic()) {
                $php .= ' = '.var_export($argument->getDefault(), true);
            }
            return $php;
        }, $arguments);
    }
}
