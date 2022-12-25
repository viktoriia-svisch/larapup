<?php
namespace Psy\Command\ListCommand;
use Psy\VarDumper\Presenter;
use Symfony\Component\Console\Input\InputInterface;
class InterfaceEnumerator extends Enumerator
{
    public function __construct(Presenter $presenter)
    {
        @\trigger_error('InterfaceEnumerator is no longer used', E_USER_DEPRECATED);
        parent::__construct($presenter);
    }
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('interfaces')) {
            return;
        }
        $interfaces = $this->prepareInterfaces(\get_declared_interfaces());
        if (empty($interfaces)) {
            return;
        }
        return [
            'Interfaces' => $interfaces,
        ];
    }
    protected function prepareInterfaces(array $interfaces)
    {
        \natcasesort($interfaces);
        $ret = [];
        foreach ($interfaces as $name) {
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
