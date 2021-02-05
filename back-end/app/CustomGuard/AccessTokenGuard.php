<?php
namespace App\Providers;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
class AccessTokenGuard
{
    use GuardHelpers;
    private $inputKey = '';
    private $storageKey = '';
    private $request;
    public function __construct(UserProvider $provider, Request  $request, $configuration)
    {
        $this->provider = $provider;
        $this->request = $request;
        $this->inputKey = isset($configuration['input_key']) ? $configuration['input_key'] : 'access_token';
        $this->storageKey = isset($configuration['storage_key']) ? $configuration['storage_key'] : 'access_token';
    }
    public function user () {
        if (!is_null($this->user)) {
            return $this->user;
        }
        $user = null;
        $token = $this->getTokenForRequest();
        if (!empty($token)) {
            $user = $this->provider->retrieveByToken($this->storageKey, $token);
        }
        return $this->user = $user;
    }
    public function getTokenForRequest () {
        $token = $this->request->query($this->inputKey);
        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }
        if (empty($token)) {
            $token = $this->request->bearerToken();
        }
        return $token;
    }
    public function validate (array $credentials = []) {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }
        $credentials = [ $this->storageKey => $credentials[$this->inputKey] ];
        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }
        return false;
    }
}
