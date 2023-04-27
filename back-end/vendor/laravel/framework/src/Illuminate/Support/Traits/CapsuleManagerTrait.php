<?php
namespace Illuminate\Support\Traits;
use Illuminate\Support\Fluent;
use Illuminate\Contracts\Container\Container;
trait CapsuleManagerTrait
{
    protected static $instance;
    protected $container;
    protected function setupContainer(Container $container)
    {
        $this->container = $container;
        if (! $this->container->bound('config')) {
            $this->container->instance('config', new Fluent);
        }
    }
    public function setAsGlobal()
    {
        static::$instance = $this;
    }
    public function getContainer()
    {
        return $this->container;
    }
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
