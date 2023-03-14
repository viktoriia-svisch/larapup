<?php
namespace Illuminate\View\Engines;
use Closure;
use InvalidArgumentException;
class EngineResolver
{
    protected $resolvers = [];
    protected $resolved = [];
    public function register($engine, Closure $resolver)
    {
        unset($this->resolved[$engine]);
        $this->resolvers[$engine] = $resolver;
    }
    public function resolve($engine)
    {
        if (isset($this->resolved[$engine])) {
            return $this->resolved[$engine];
        }
        if (isset($this->resolvers[$engine])) {
            return $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
        }
        throw new InvalidArgumentException("Engine [{$engine}] not found.");
    }
}
