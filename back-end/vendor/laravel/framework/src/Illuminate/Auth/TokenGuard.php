<?php
namespace Illuminate\Auth;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
class TokenGuard implements Guard
{
    use GuardHelpers;
    protected $request;
    protected $inputKey;
    protected $storageKey;
    public function __construct(UserProvider $provider, Request $request, $inputKey = 'api_token', $storageKey = 'api_token')
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->storageKey = $storageKey;
    }
    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }
        $user = null;
        $token = $this->getTokenForRequest();
        if (! empty($token)) {
            $user = $this->provider->retrieveByCredentials(
                [$this->storageKey => $token]
            );
        }
        return $this->user = $user;
    }
    public function getTokenForRequest()
    {
        $token = $this->request->query($this->inputKey);
        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }
        if (empty($token)) {
            $token = $this->request->bearerToken();
        }
        if (empty($token)) {
            $token = $this->request->getPassword();
        }
        return $token;
    }
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }
        $credentials = [$this->storageKey => $credentials[$this->inputKey]];
        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }
        return false;
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
