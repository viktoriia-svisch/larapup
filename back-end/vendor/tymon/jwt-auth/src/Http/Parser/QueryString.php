<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\Http\Parser as ParserContract;
class QueryString implements ParserContract
{
    use KeyTrait;
    public function parse(Request $request)
    {
        return $request->query($this->key);
    }
}
