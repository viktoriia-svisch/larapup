<?php
namespace Illuminate\Routing\Middleware;
use Closure;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
class ValidateSignature
{
    public function handle($request, Closure $next)
    {
        if ($request->hasValidSignature()) {
            return $next($request);
        }
        throw new InvalidSignatureException;
    }
}
