<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class MethodEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector === null) {
            return;
        }
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }
        if (!$input->getOption('methods')) {
            return;
        }
        $showAll   = $input->getOption('all');
        $noInherit = $input->getOption('no-inherit');
        $methods   = $this->prepareMethods($this->getMethods($showAll, $reflector, $noInherit));
        if (empty($methods)) {
            return;
        }
        $ret = [];
        $ret[$this->getKindLabel($reflector)] = $methods;
        return $ret;
    }
    protected function getMethods($showAll, \Reflector $reflector, $noInherit = false)
    {
        $className = $reflector->getName();
        $methods = [];
        foreach ($reflector->getMethods() as $name => $method) {
            if ($noInherit && $method->getDeclaringClass()->getName() !== $className) {
                continue;
            }
            if ($showAll || $method->isPublic()) {
                $methods[$method->getName()] = $method;
            }
        }
        \ksort($methods, SORT_NATURAL | SORT_FLAG_CASE);
        return $methods;
    }
    protected function prepareMethods(array $methods)
    {
        $ret = [];
        foreach ($methods as $name => $method) {
            if ($this->showItem($name)) {
                $ret[$name] = [
                    'name'  => $name,
                    'style' => $this->getVisibilityStyle($method),
                    'value' => $this->presentSignature($method),
                ];
            }
        }
        return $ret;
    }
    protected function getKindLabel(\ReflectionClass $reflector)
    {
        if ($reflector->isInterface()) {
            return 'Interface Methods';
        } elseif (\method_exists($reflector, 'isTrait') && $reflector->isTrait()) {
            return 'Trait Methods';
        } else {
            return 'Class Methods';
        }
    }
    private function getVisibilityStyle(\ReflectionMethod $method)
    {
        if ($method->isPublic()) {
            return self::IS_PUBLIC;
        } elseif ($method->isProtected()) {
            return self::IS_PROTECTED;
        } else {
            return self::IS_PRIVATE;
        }
    }
}
