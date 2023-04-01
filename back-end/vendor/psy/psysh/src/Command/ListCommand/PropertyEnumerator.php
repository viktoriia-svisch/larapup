<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class PropertyEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector === null) {
            return;
        }
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }
        if (!$input->getOption('properties')) {
            return;
        }
        $showAll    = $input->getOption('all');
        $noInherit  = $input->getOption('no-inherit');
        $properties = $this->prepareProperties($this->getProperties($showAll, $reflector, $noInherit), $target);
        if (empty($properties)) {
            return;
        }
        $ret = [];
        $ret[$this->getKindLabel($reflector)] = $properties;
        return $ret;
    }
    protected function getProperties($showAll, \Reflector $reflector, $noInherit = false)
    {
        $className = $reflector->getName();
        $properties = [];
        foreach ($reflector->getProperties() as $property) {
            if ($noInherit && $property->getDeclaringClass()->getName() !== $className) {
                continue;
            }
            if ($showAll || $property->isPublic()) {
                $properties[$property->getName()] = $property;
            }
        }
        \ksort($properties, SORT_NATURAL | SORT_FLAG_CASE);
        return $properties;
    }
    protected function prepareProperties(array $properties, $target = null)
    {
        $ret = [];
        foreach ($properties as $name => $property) {
            if ($this->showItem($name)) {
                $fname = '$' . $name;
                $ret[$fname] = [
                    'name'  => $fname,
                    'style' => $this->getVisibilityStyle($property),
                    'value' => $this->presentValue($property, $target),
                ];
            }
        }
        return $ret;
    }
    protected function getKindLabel(\ReflectionClass $reflector)
    {
        if ($reflector->isInterface()) {
            return 'Interface Properties';
        } elseif (\method_exists($reflector, 'isTrait') && $reflector->isTrait()) {
            return 'Trait Properties';
        } else {
            return 'Class Properties';
        }
    }
    private function getVisibilityStyle(\ReflectionProperty $property)
    {
        if ($property->isPublic()) {
            return self::IS_PUBLIC;
        } elseif ($property->isProtected()) {
            return self::IS_PROTECTED;
        } else {
            return self::IS_PRIVATE;
        }
    }
    protected function presentValue(\ReflectionProperty $property, $target)
    {
        if (!\is_object($target)) {
            try {
                $refl = new \ReflectionClass($target);
                $props = $refl->getDefaultProperties();
                if (\array_key_exists($property->name, $props)) {
                    $suffix = $property->isStatic() ? '' : ' <aside>(default)</aside>';
                    return $this->presentRef($props[$property->name]) . $suffix;
                }
            } catch (\Exception $e) {
            }
            return '';
        }
        $property->setAccessible(true);
        $value = $property->getValue($target);
        return $this->presentRef($value);
    }
}
