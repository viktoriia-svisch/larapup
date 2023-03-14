<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\Http\Parser as ParserContract;
class InputSource implements ParserContract
{
    use KeyTrait;
    public function parse(Request $request)
    {
        return $request->input($this->key);
    }
}
