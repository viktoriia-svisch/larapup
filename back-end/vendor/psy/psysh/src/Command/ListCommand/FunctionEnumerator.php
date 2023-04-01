<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class FunctionEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('functions')) {
            return;
        }
        if ($input->getOption('user')) {
            $label     = 'User Functions';
            $functions = $this->getFunctions('user');
        } elseif ($input->getOption('internal')) {
            $label     = 'Internal Functions';
            $functions = $this->getFunctions('internal');
        } else {
            $label     = 'Functions';
            $functions = $this->getFunctions();
        }
        $functions = $this->prepareFunctions($functions);
        if (empty($functions)) {
            return;
        }
        $ret = [];
        $ret[$label] = $functions;
        return $ret;
    }
    protected function getFunctions($type = null)
    {
        $funcs = \get_defined_functions();
        if ($type) {
            return $funcs[$type];
        } else {
            return \array_merge($funcs['internal'], $funcs['user']);
        }
    }
    protected function prepareFunctions(array $functions)
    {
        \natcasesort($functions);
        $ret = [];
        foreach ($functions as $name) {
            if ($this->showItem($name)) {
                $ret[$name] = [
                    'name'  => $name,
                    'style' => self::IS_FUNCTION,
                    'value' => $this->presentSignature($name),
                ];
            }
        }
        return $ret;
    }
}
