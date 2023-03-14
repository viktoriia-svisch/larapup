<?php
namespace Illuminate\Foundation\Auth\Access;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Access\Gate;
trait AuthorizesRequests
{
    public function authorize($ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);
        return app(Gate::class)->authorize($ability, $arguments);
    }
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);
        return app(Gate::class)->forUser($user)->authorize($ability, $arguments);
    }
    protected function parseAbilityAndArguments($ability, $arguments)
    {
        if (is_string($ability) && strpos($ability, '\\') === false) {
            return [$ability, $arguments];
        }
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
        return [$this->normalizeGuessedAbilityName($method), $ability];
    }
    protected function normalizeGuessedAbilityName($ability)
    {
        $map = $this->resourceAbilityMap();
        return $map[$ability] ?? $ability;
    }
    public function authorizeResource($model, $parameter = null, array $options = [], $request = null)
    {
        $parameter = $parameter ?: Str::snake(class_basename($model));
        $middleware = [];
        foreach ($this->resourceAbilityMap() as $method => $ability) {
            $modelName = in_array($method, $this->resourceMethodsWithoutModels()) ? $model : $parameter;
            $middleware["can:{$ability},{$modelName}"][] = $method;
        }
        foreach ($middleware as $middlewareName => $methods) {
            $this->middleware($middlewareName, $options)->only($methods);
        }
    }
    protected function resourceAbilityMap()
    {
        return [
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];
    }
    protected function resourceMethodsWithoutModels()
    {
        return ['index', 'create', 'store'];
    }
}
