<?php
namespace Illuminate\Pipeline;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pipeline\Hub as HubContract;
class Hub implements HubContract
{
    protected $container;
    protected $pipelines = [];
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }
    public function defaults(Closure $callback)
    {
        return $this->pipeline('default', $callback);
    }
    public function pipeline($name, Closure $callback)
    {
        $this->pipelines[$name] = $callback;
    }
    public function pipe($object, $pipeline = null)
    {
        $pipeline = $pipeline ?: 'default';
        return call_user_func(
            $this->pipelines[$pipeline], new Pipeline($this->container), $object
        );
    }
}
