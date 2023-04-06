<?php
namespace Illuminate\Foundation\Http\Middleware;
use Closure;
use Symfony\Component\HttpFoundation\ParameterBag;
class TransformsRequest
{
    protected $attributes = [];
    public function handle($request, Closure $next, ...$attributes)
    {
        $this->attributes = $attributes;
        $this->clean($request);
        return $next($request);
    }
    protected function clean($request)
    {
        $this->cleanParameterBag($request->query);
        if ($request->isJson()) {
            $this->cleanParameterBag($request->json());
        } elseif ($request->request !== $request->query) {
            $this->cleanParameterBag($request->request);
        }
    }
    protected function cleanParameterBag(ParameterBag $bag)
    {
        $bag->replace($this->cleanArray($bag->all()));
    }
    protected function cleanArray(array $data)
    {
        return collect($data)->map(function ($value, $key) {
            return $this->cleanValue($key, $value);
        })->all();
    }
    protected function cleanValue($key, $value)
    {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }
        return $this->transform($key, $value);
    }
    protected function transform($key, $value)
    {
        return $value;
    }
}
