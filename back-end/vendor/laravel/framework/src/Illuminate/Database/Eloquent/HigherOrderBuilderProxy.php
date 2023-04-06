<?php
namespace Illuminate\Database\Eloquent;
class HigherOrderBuilderProxy
{
    protected $builder;
    protected $method;
    public function __construct(Builder $builder, $method)
    {
        $this->method = $method;
        $this->builder = $builder;
    }
    public function __call($method, $parameters)
    {
        return $this->builder->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}
