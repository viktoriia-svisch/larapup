<?php
namespace Tymon\JWTAuth\Http\Parser;
use Illuminate\Http\Request;
class Parser
{
    private $chain;
    protected $request;
    public function __construct(Request $request, array $chain = [])
    {
        $this->request = $request;
        $this->chain = $chain;
    }
    public function getChain()
    {
        return $this->chain;
    }
    public function setChain(array $chain)
    {
        $this->chain = $chain;
        return $this;
    }
    public function setChainOrder(array $chain)
    {
        return $this->setChain($chain);
    }
    public function parseToken()
    {
        foreach ($this->chain as $parser) {
            if ($response = $parser->parse($this->request)) {
                return $response;
            }
        }
    }
    public function hasToken()
    {
        return $this->parseToken() !== null;
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
