<?php
namespace Tymon\JWTAuth\Contracts\Http;
use Illuminate\Http\Request;
interface Parser
{
    public function parse(Request $request);
}
