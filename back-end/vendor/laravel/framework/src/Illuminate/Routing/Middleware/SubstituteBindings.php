<?php
namespace Illuminate\Routing\Middleware;
use Closure;
use Illuminate\Contracts\Routing\Registrar;
class SubstituteBindings
{
    protected $router;
    public function __construct(Registrar $router)
    {
        $this->router = $router;
    }
    public function handle($request, Closure $next)
    {
        $this->router->substituteBindings($route = $request->route());
        $this->router->substituteImplicitBindings($route);
        return $next($request);
    }
}
