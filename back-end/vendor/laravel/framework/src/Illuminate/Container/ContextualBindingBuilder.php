<?php
namespace Illuminate\Container;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;
class ContextualBindingBuilder implements ContextualBindingBuilderContract
{
    protected $container;
    protected $concrete;
    protected $needs;
    public function __construct(Container $container, $concrete)
    {
        $this->concrete = $concrete;
        $this->container = $container;
    }
    public function needs($abstract)
    {
        $this->needs = $abstract;
        return $this;
    }
    public function give($implementation)
    {
        foreach (Arr::wrap($this->concrete) as $concrete) {
            $this->container->addContextualBinding($concrete, $this->needs, $implementation);
        }
    }
}
