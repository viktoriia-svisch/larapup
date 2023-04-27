<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
class LumenRouteParams extends RouteParams
{
    public function parse(Request $request)
    {
        return Arr::get($request->route(), '2.'.$this->key);
    }
}
