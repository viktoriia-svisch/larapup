<?php
namespace Psy\Command\ListCommand;
use Psy\Formatter\SignatureFormatter;
use Psy\Input\FilterOptions;
use Psy\Util\Mirror;
use Psy\VarDumper\Presenter;
use Symfony\Component\Console\Input\InputInterface;
abstract class Enumerator
{
    const IS_PUBLIC    = 'public';
    const IS_PROTECTED = 'protected';
    const IS_PRIVATE   = 'private';
    const IS_GLOBAL    = 'global';
    const IS_CONSTANT  = 'const';
    const IS_CLASS     = 'class';
    const IS_FUNCTION  = 'function';
    private $filter;
    private $presenter;
    public function __construct(Presenter $presenter)
    {
        $this->filter = new FilterOptions();
        $this->presenter = $presenter;
    }
    public function enumerate(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        $this->filter->bind($input);
        return $this->listItems($input, $reflector, $target);
    }
    abstract protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null);
    protected function showItem($name)
    {
        return $this->filter->match($name);
    }
    protected function presentRef($value)
    {
        return $this->presenter->presentRef($value);
    }
    protected function presentSignature($target)
    {
        if (!$target instanceof \Reflector) {
            $target = Mirror::get($target);
        }
        return SignatureFormatter::format($target);
    }
}
