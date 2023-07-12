<?php
namespace Illuminate\Routing\Matching;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
class SchemeValidator implements ValidatorInterface
{
    public function matches(Route $route, Request $request)
    {
        if ($route->httpOnly()) {
            return ! $request->secure();
        } elseif ($route->secure()) {
            return $request->secure();
        }
        return true;
    }
}
