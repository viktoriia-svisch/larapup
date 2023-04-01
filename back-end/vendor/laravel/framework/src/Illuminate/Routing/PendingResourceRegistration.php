<?php
namespace Illuminate\Routing;
use Illuminate\Support\Traits\Macroable;
class PendingResourceRegistration
{
    use Macroable;
    protected $registrar;
    protected $name;
    protected $controller;
    protected $options = [];
    protected $registered = false;
    public function __construct(ResourceRegistrar $registrar, $name, $controller, array $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->registrar = $registrar;
        $this->controller = $controller;
    }
    public function only($methods)
    {
        $this->options['only'] = is_array($methods) ? $methods : func_get_args();
        return $this;
    }
    public function except($methods)
    {
        $this->options['except'] = is_array($methods) ? $methods : func_get_args();
        return $this;
    }
    public function names($names)
    {
        $this->options['names'] = $names;
        return $this;
    }
    public function name($method, $name)
    {
        $this->options['names'][$method] = $name;
        return $this;
    }
    public function parameters($parameters)
    {
        $this->options['parameters'] = $parameters;
        return $this;
    }
    public function parameter($previous, $new)
    {
        $this->options['parameters'][$previous] = $new;
        return $this;
    }
    public function middleware($middleware)
    {
        $this->options['middleware'] = $middleware;
        return $this;
    }
    public function register()
    {
        $this->registered = true;
        return $this->registrar->register(
            $this->name, $this->controller, $this->options
        );
    }
    public function __destruct()
    {
        if (! $this->registered) {
            $this->register();
        }
    }
}
