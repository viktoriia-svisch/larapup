<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Contracts\Http\Parser as ParserContract;
class Cookies implements ParserContract
{
    use KeyTrait;
    private $decrypt;
    public function __construct($decrypt = true)
    {
        $this->decrypt = $decrypt;
    }
    public function parse(Request $request)
    {
        if ($this->decrypt && $request->hasCookie($this->key)) {
            return Crypt::decrypt($request->cookie($this->key));
        }
        return $request->cookie($this->key);
    }
}
