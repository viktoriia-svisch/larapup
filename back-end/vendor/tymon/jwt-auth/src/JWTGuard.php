<?php
namespace Tymon\JWTAuth;
use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Traits\Macroable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Contracts\Auth\UserProvider;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
class JWTGuard implements Guard
{
    use GuardHelpers, Macroable {
        __call as macroCall;
    }
    protected $lastAttempted;
    protected $jwt;
    protected $request;
    public function __construct(JWT $jwt, UserProvider $provider, Request $request)
    {
        $this->jwt = $jwt;
        $this->provider = $provider;
        $this->request = $request;
    }
    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }
        if ($this->jwt->setRequest($this->request)->getToken() &&
            ($payload = $this->jwt->check(true)) &&
            $this->validateSubject()
        ) {
            return $this->user = $this->provider->retrieveById($payload['sub']);
        }
    }
    public function userOrFail()
    {
        if (! $user = $this->user()) {
            throw new UserNotDefinedException;
        }
        return $user;
    }
    public function validate(array $credentials = [])
    {
        return (bool) $this->attempt($credentials, false);
    }
    public function attempt(array $credentials = [], $login = true)
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            return $login ? $this->login($user) : true;
        }
        return false;
    }
    public function login(JWTSubject $user)
    {
        $token = $this->jwt->fromUser($user);
        $this->setToken($token)->setUser($user);
        return $token;
    }
    public function logout($forceForever = false)
    {
        $this->requireToken()->invalidate($forceForever);
        $this->user = null;
        $this->jwt->unsetToken();
    }
    public function refresh($forceForever = false, $resetClaims = false)
    {
        return $this->requireToken()->refresh($forceForever, $resetClaims);
    }
    public function invalidate($forceForever = false)
    {
        return $this->requireToken()->invalidate($forceForever);
    }
    public function tokenById($id)
    {
        if ($user = $this->provider->retrieveById($id)) {
            return $this->jwt->fromUser($user);
        }
    }
    public function once(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);
            return true;
        }
        return false;
    }
    public function onceUsingId($id)
    {
        if ($user = $this->provider->retrieveById($id)) {
            $this->setUser($user);
            return true;
        }
        return false;
    }
    public function byId($id)
    {
        return $this->onceUsingId($id);
    }
    public function claims(array $claims)
    {
        $this->jwt->claims($claims);
        return $this;
    }
    public function getPayload()
    {
        return $this->requireToken()->getPayload();
    }
    public function payload()
    {
        return $this->getPayload();
    }
    public function setToken($token)
    {
        $this->jwt->setToken($token);
        return $this;
    }
    public function setTTL($ttl)
    {
        $this->jwt->factory()->setTTL($ttl);
        return $this;
    }
    public function getProvider()
    {
        return $this->provider;
    }
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
        return $this;
    }
    public function getUser()
    {
        return $this->user;
    }
    public function getRequest()
    {
        return $this->request ?: Request::createFromGlobals();
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
    public function getLastAttempted()
    {
        return $this->lastAttempted;
    }
    protected function hasValidCredentials($user, $credentials)
    {
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }
    protected function validateSubject()
    {
        if (! method_exists($this->provider, 'getModel')) {
            return true;
        }
        return $this->jwt->checkSubjectModel($this->provider->getModel());
    }
    protected function requireToken()
    {
        if (! $this->jwt->setRequest($this->getRequest())->getToken()) {
            throw new JWTException('Token could not be parsed from the request.');
        }
        return $this->jwt;
    }
    public function __call($method, $parameters)
    {
        if (method_exists($this->jwt, $method)) {
            return call_user_func_array([$this->jwt, $method], $parameters);
        }
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
