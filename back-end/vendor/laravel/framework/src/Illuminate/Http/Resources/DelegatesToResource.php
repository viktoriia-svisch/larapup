<?php
namespace Illuminate\Http\Resources;
use Exception;
use Illuminate\Support\Traits\ForwardsCalls;
trait DelegatesToResource
{
    use ForwardsCalls;
    public function getRouteKey()
    {
        return $this->resource->getRouteKey();
    }
    public function getRouteKeyName()
    {
        return $this->resource->getRouteKeyName();
    }
    public function resolveRouteBinding($value)
    {
        throw new Exception('Resources may not be implicitly resolved from route bindings.');
    }
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->resource);
    }
    public function offsetGet($offset)
    {
        return $this->resource[$offset];
    }
    public function offsetSet($offset, $value)
    {
        $this->resource[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        unset($this->resource[$offset]);
    }
    public function __isset($key)
    {
        return isset($this->resource->{$key});
    }
    public function __unset($key)
    {
        unset($this->resource->{$key});
    }
    public function __get($key)
    {
        return $this->resource->{$key};
    }
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->resource, $method, $parameters);
    }
}
