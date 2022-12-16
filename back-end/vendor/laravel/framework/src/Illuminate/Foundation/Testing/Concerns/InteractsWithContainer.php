<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Closure;
use Mockery;
trait InteractsWithContainer
{
    protected function swap($abstract, $instance)
    {
        return $this->instance($abstract, $instance);
    }
    protected function instance($abstract, $instance)
    {
        $this->app->instance($abstract, $instance);
        return $instance;
    }
    protected function mock($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }
    protected function spy($abstract, Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::spy(...array_filter(func_get_args())));
    }
}
