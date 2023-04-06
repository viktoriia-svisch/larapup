<?php
namespace Symfony\Component\HttpKernel\ControllerMetadata;
final class ArgumentMetadataFactory implements ArgumentMetadataFactoryInterface
{
    public function createArgumentMetadata($controller)
    {
        $arguments = [];
        if (\is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (\is_object($controller) && !$controller instanceof \Closure) {
            $reflection = (new \ReflectionObject($controller))->getMethod('__invoke');
        } else {
            $reflection = new \ReflectionFunction($controller);
        }
        foreach ($reflection->getParameters() as $param) {
            $arguments[] = new ArgumentMetadata($param->getName(), $this->getType($param, $reflection), $param->isVariadic(), $param->isDefaultValueAvailable(), $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null, $param->allowsNull());
        }
        return $arguments;
    }
    private function getType(\ReflectionParameter $parameter, \ReflectionFunctionAbstract $function)
    {
        if (!$type = $parameter->getType()) {
            return;
        }
        $name = $type->getName();
        $lcName = strtolower($name);
        if ('self' !== $lcName && 'parent' !== $lcName) {
            return $name;
        }
        if (!$function instanceof \ReflectionMethod) {
            return;
        }
        if ('self' === $lcName) {
            return $function->getDeclaringClass()->name;
        }
        if ($parent = $function->getDeclaringClass()->getParentClass()) {
            return $parent->name;
        }
    }
}
