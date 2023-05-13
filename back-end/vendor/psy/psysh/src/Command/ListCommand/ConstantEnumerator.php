<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class ConstantEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('constants')) {
            return;
        }
        $user     = $input->getOption('user');
        $internal = $input->getOption('internal');
        $category = $input->getOption('category');
        $ret = [];
        if ($user) {
            $ret['User Constants'] = $this->getConstants('user');
        }
        if ($internal) {
            $ret['Interal Constants'] = $this->getConstants('internal');
        }
        if ($category) {
            $label = \ucfirst($category) . ' Constants';
            $ret[$label] = $this->getConstants($category);
        }
        if (!$user && !$internal && !$category) {
            $ret['Constants'] = $this->getConstants();
        }
        return \array_map([$this, 'prepareConstants'], \array_filter($ret));
    }
    protected function getConstants($category = null)
    {
        if (!$category) {
            return \get_defined_constants();
        }
        $consts = \get_defined_constants(true);
        if ($category === 'internal') {
            unset($consts['user']);
            return \call_user_func_array('array_merge', $consts);
        }
        return isset($consts[$category]) ? $consts[$category] : [];
    }
    protected function prepareConstants(array $constants)
    {
        $ret = [];
        $names = \array_keys($constants);
        \natcasesort($names);
        foreach ($names as $name) {
            if ($this->showItem($name)) {
                $ret[$name] = [
                    'name'  => $name,
                    'style' => self::IS_CONSTANT,
                    'value' => $this->presentRef($constants[$name]),
                ];
            }
        }
        return $ret;
    }
}
