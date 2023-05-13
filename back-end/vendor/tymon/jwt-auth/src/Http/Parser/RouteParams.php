<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\Http\Parser as ParserContract;
class RouteParams implements ParserContract
{
    use KeyTrait;
    public function parse(Request $request)
    {
        $route = $request->route();
        if (is_callable([$route, 'parameter'])) {
            return $route->parameter($this->key);
        }
    }
}
