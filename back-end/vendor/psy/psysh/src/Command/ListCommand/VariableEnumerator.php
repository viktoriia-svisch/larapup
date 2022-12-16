<?php
namespace Psy\Command\ListCommand;
use Psy\Context;
use Psy\VarDumper\Presenter;
use Symfony\Component\Console\Input\InputInterface;
class VariableEnumerator extends Enumerator
{
    private static $specialNames = [
        '_', '_e', '__out', '__function', '__method', '__class', '__namespace', '__file', '__line', '__dir',
    ];
    private $context;
    public function __construct(Presenter $presenter, Context $context)
    {
        $this->context = $context;
        parent::__construct($presenter);
    }
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('vars')) {
            return;
        }
        $showAll   = $input->getOption('all');
        $variables = $this->prepareVariables($this->getVariables($showAll));
        if (empty($variables)) {
            return;
        }
        return [
            'Variables' => $variables,
        ];
    }
    protected function getVariables($showAll)
    {
        $scopeVars = $this->context->getAll();
        \uksort($scopeVars, function ($a, $b) {
            $aIndex = \array_search($a, self::$specialNames);
            $bIndex = \array_search($b, self::$specialNames);
            if ($aIndex !== false) {
                if ($bIndex !== false) {
                    return $aIndex - $bIndex;
                }
                return 1;
            }
            if ($bIndex !== false) {
                return -1;
            }
            return \strnatcasecmp($a, $b);
        });
        $ret = [];
        foreach ($scopeVars as $name => $val) {
            if (!$showAll && \in_array($name, self::$specialNames)) {
                continue;
            }
            $ret[$name] = $val;
        }
        return $ret;
    }
    protected function prepareVariables(array $variables)
    {
        $ret = [];
        foreach ($variables as $name => $val) {
            if ($this->showItem($name)) {
                $fname = '$' . $name;
                $ret[$fname] = [
                    'name'  => $fname,
                    'style' => \in_array($name, self::$specialNames) ? self::IS_PRIVATE : self::IS_PUBLIC,
                    'value' => $this->presentRef($val),
                ];
            }
        }
        return $ret;
    }
}
