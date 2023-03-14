<?php
namespace Illuminate\Routing\Matching;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
class UriValidator implements ValidatorInterface
{
    public function matches(Route $route, Request $request)
    {
        $path = $request->path() === '/' ? '/' : '/'.$request->path();
        return preg_match($route->getCompiled()->getRegex(), rawurldecode($path));
    }
}
