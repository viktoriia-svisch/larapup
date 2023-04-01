<?php
namespace Illuminate\Auth\Middleware;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Gate;
class Authorize
{
    protected $gate;
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }
    public function handle($request, Closure $next, $ability, ...$models)
    {
        $this->gate->authorize($ability, $this->getGateArguments($request, $models));
        return $next($request);
    }
    protected function getGateArguments($request, $models)
    {
        if (is_null($models)) {
            return [];
        }
        return collect($models)->map(function ($model) use ($request) {
            return $model instanceof Model ? $model : $this->getModel($request, $model);
        })->all();
    }
    protected function getModel($request, $model)
    {
        return $this->isClassName($model) ? trim($model) : $request->route($model, $model);
    }
    protected function isClassName($value)
    {
        return strpos($value, '\\') !== false;
    }
}
