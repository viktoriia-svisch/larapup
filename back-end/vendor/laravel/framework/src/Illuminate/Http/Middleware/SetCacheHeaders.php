<?php
namespace Illuminate\Http\Middleware;
use Closure;
class SetCacheHeaders
{
    public function handle($request, Closure $next, $options = [])
    {
        $response = $next($request);
        if (! $request->isMethodCacheable() || ! $response->getContent()) {
            return $response;
        }
        if (is_string($options)) {
            $options = $this->parseOptions($options);
        }
        if (isset($options['etag']) && $options['etag'] === true) {
            $options['etag'] = md5($response->getContent());
        }
        $response->setCache($options);
        $response->isNotModified($request);
        return $response;
    }
    protected function parseOptions($options)
    {
        return collect(explode(';', $options))->mapWithKeys(function ($option) {
            $data = explode('=', $option, 2);
            return [$data[0] => $data[1] ?? true];
        })->all();
    }
}
