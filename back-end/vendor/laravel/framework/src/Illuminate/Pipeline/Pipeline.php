<?php
namespace Illuminate\Pipeline;
use Closure;
use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
class Pipeline implements PipelineContract
{
    protected $container;
    protected $passable;
    protected $pipes = [];
    protected $method = 'handle';
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }
    public function send($passable)
    {
        $this->passable = $passable;
        return $this;
    }
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }
    public function via($method)
    {
        $this->method = $method;
        return $this;
    }
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
        );
        return $pipeline($this->passable);
    }
    public function thenReturn()
    {
        return $this->then(function ($passable) {
            return $passable;
        });
    }
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            return $destination($passable);
        };
    }
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                } elseif (! is_object($pipe)) {
                    [$name, $parameters] = $this->parsePipeString($pipe);
                    $pipe = $this->getContainer()->make($name);
                    $parameters = array_merge([$passable, $stack], $parameters);
                } else {
                    $parameters = [$passable, $stack];
                }
                $response = method_exists($pipe, $this->method)
                                ? $pipe->{$this->method}(...$parameters)
                                : $pipe(...$parameters);
                return $response instanceof Responsable
                            ? $response->toResponse($this->getContainer()->make(Request::class))
                            : $response;
            };
        };
    }
    protected function parsePipeString($pipe)
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);
        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }
        return [$name, $parameters];
    }
    protected function getContainer()
    {
        if (! $this->container) {
            throw new RuntimeException('A container instance has not been passed to the Pipeline.');
        }
        return $this->container;
    }
}
