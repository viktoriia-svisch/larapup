<?php
namespace Illuminate\Foundation\Auth\Access;
use Illuminate\Contracts\Auth\Access\Gate;
trait Authorizable
{
    public function can($ability, $arguments = [])
    {
        return app(Gate::class)->forUser($this)->check($ability, $arguments);
    }
    public function cant($ability, $arguments = [])
    {
        return ! $this->can($ability, $arguments);
    }
    public function cannot($ability, $arguments = [])
    {
        return $this->cant($ability, $arguments);
    }
}
