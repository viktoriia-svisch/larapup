<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\Http\Parser as ParserContract;
class AuthHeaders implements ParserContract
{
    protected $header = 'authorization';
    protected $prefix = 'bearer';
    protected function fromAltHeaders(Request $request)
    {
        return $request->server->get('HTTP_AUTHORIZATION') ?: $request->server->get('REDIRECT_HTTP_AUTHORIZATION');
    }
    public function parse(Request $request)
    {
        $header = $request->headers->get($this->header) ?: $this->fromAltHeaders($request);
        if ($header && preg_match('/'.$this->prefix.'\s*(\S+)\b/i', $header, $matches)) {
            return $matches[1];
        }
    }
    public function setHeaderName($headerName)
    {
        $this->header = $headerName;
        return $this;
    }
    public function setHeaderPrefix($headerPrefix)
    {
        $this->prefix = $headerPrefix;
        return $this;
    }
}
