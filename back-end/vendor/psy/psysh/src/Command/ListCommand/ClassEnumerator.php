<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class ClassEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        $user     = $input->getOption('user');
        $internal = $input->getOption('internal');
        $ret = [];
        if ($input->getOption('classes')) {
            $ret = \array_merge($ret, $this->filterClasses('Classes', \get_declared_classes(), $internal, $user));
        }
        if ($input->getOption('interfaces')) {
            $ret = \array_merge($ret, $this->filterClasses('Interfaces', \get_declared_interfaces(), $internal, $user));
        }
        if ($input->getOption('traits')) {
            $ret = \array_merge($ret, $this->filterClasses('Traits', \get_declared_traits(), $internal, $user));
        }
        return \array_map([$this, 'prepareClasses'], \array_filter($ret));
    }
    protected function filterClasses($key, $classes, $internal, $user)
    {
        $ret = [];
        if ($internal) {
            $ret['Internal ' . $key] = \array_filter($classes, function ($class) {
                $refl = new \ReflectionClass($class);
                return $refl->isInternal();
            });
        }
        if ($user) {
            $ret['User ' . $key] = \array_filter($classes, function ($class) {
                $refl = new \ReflectionClass($class);
                return !$refl->isInternal();
            });
        }
        if (!$user && !$internal) {
            $ret[$key] = $classes;
        }
        return $ret;
    }
    protected function prepareClasses(array $classes)
    {
        \natcasesort($classes);
        $ret = [];
        foreach ($classes as $name) {
            if ($this->showItem($name)) {
                $ret[$name] = [
                    'name'  => $name,
                    'style' => self::IS_CLASS,
                    'value' => $this->presentSignature($name),
                ];
            }
        }
        return $ret;
    }
}
