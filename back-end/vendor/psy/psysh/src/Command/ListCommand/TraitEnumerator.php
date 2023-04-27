<?php
namespace Psy\Command\ListCommand;
use Psy\VarDumper\Presenter;
use Symfony\Component\Console\Input\InputInterface;
class TraitEnumerator extends Enumerator
{
    public function __construct(Presenter $presenter)
    {
        @\trigger_error('TraitEnumerator is no longer used', E_USER_DEPRECATED);
        parent::__construct($presenter);
    }
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('traits')) {
            return;
        }
        $traits = $this->prepareTraits(\get_declared_traits());
        if (empty($traits)) {
            return;
        }
        return [
            'Traits' => $traits,
        ];
    }
    protected function prepareTraits(array $traits)
    {
        \natcasesort($traits);
        $ret = [];
        foreach ($traits as $name) {
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
