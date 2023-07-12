<?php
namespace Illuminate\Auth;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Auth\UserProvider;
class RequestGuard implements Guard
{
    use GuardHelpers, Macroable;
    protected $callback;
    protected $request;
    public function __construct(callable $callback, Request $request, UserProvider $provider = null)
    {
        $this->request = $request;
        $this->callback = $callback;
        $this->provider = $provider;
    }
    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }
        return $this->user = call_user_func(
            $this->callback, $this->request, $this->getProvider()
        );
    }
    public function validate(array $credentials = [])
    {
        return ! is_null((new static(
            $this->callback, $credentials['request'], $this->getProvider()
        ))->user());
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
