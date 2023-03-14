<?php
namespace Psy\Command\ListCommand;
use Psy\Reflection\ReflectionClassConstant;
use Symfony\Component\Console\Input\InputInterface;
class ClassConstantEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector === null) {
            return;
        }
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }
        if (!$input->getOption('constants')) {
            return;
        }
        $noInherit = $input->getOption('no-inherit');
        $constants = $this->prepareConstants($this->getConstants($reflector, $noInherit));
        if (empty($constants)) {
            return;
        }
        $ret = [];
        $ret[$this->getKindLabel($reflector)] = $constants;
        return $ret;
    }
    protected function getConstants(\Reflector $reflector, $noInherit = false)
    {
        $className = $reflector->getName();
        $constants = [];
        foreach ($reflector->getConstants() as $name => $constant) {
            $constReflector = ReflectionClassConstant::create($reflector->name, $name);
            if ($noInherit && $constReflector->getDeclaringClass()->getName() !== $className) {
                continue;
            }
            $constants[$name] = $constReflector;
        }
        \ksort($constants, SORT_NATURAL | SORT_FLAG_CASE);
        return $constants;
    }
    protected function prepareConstants(array $constants)
    {
        $ret = [];
        foreach ($constants as $name => $constant) {
            if ($this->showItem($name)) {
                $ret[$name] = [
                    'name'  => $name,
                    'style' => self::IS_CONSTANT,
                    'value' => $this->presentRef($constant->getValue()),
                ];
            }
        }
        return $ret;
    }
    protected function getKindLabel(\ReflectionClass $reflector)
    {
        if ($reflector->isInterface()) {
            return 'Interface Constants';
        } elseif (\method_exists($reflector, 'isTrait') && $reflector->isTrait()) {
            return 'Trait Constants';
        } else {
            return 'Class Constants';
        }
    }
}
