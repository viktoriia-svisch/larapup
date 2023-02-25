<?php
namespace Illuminate\Contracts\Auth\Access;
interface Gate
{
    public function has($ability);
    public function define($ability, $callback);
    public function policy($class, $policy);
    public function before(callable $callback);
    public function after(callable $callback);
    public function allows($ability, $arguments = []);
    public function denies($ability, $arguments = []);
    public function check($abilities, $arguments = []);
    public function any($abilities, $arguments = []);
    public function authorize($ability, $arguments = []);
    public function raw($ability, $arguments = []);
    public function getPolicyFor($class);
    public function forUser($user);
    public function abilities();
}
