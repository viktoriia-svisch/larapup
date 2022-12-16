<?php
namespace Illuminate\Foundation\Bus;
class PendingChain
{
    public $class;
    public $chain;
    public function __construct($class, $chain)
    {
        $this->class = $class;
        $this->chain = $chain;
    }
    public function dispatch()
    {
        return (new PendingDispatch(
            new $this->class(...func_get_args())
        ))->chain($this->chain);
    }
}
