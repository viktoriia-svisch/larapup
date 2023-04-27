<?php
namespace Illuminate\Auth;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Auth\StatefulGuard;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Auth\SupportsBasicAuth;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
class SessionGuard implements StatefulGuard, SupportsBasicAuth
{
    use GuardHelpers, Macroable;
    protected $name;
    protected $lastAttempted;
    protected $viaRemember = false;
    protected $session;
    protected $cookie;
    protected $request;
    protected $events;
    protected $loggedOut = false;
    protected $recallAttempted = false;
    public function __construct($name,
                                UserProvider $provider,
                                Session $session,
                                Request $request = null)
    {
        $this->name = $name;
        $this->session = $session;
        $this->request = $request;
        $this->provider = $provider;
    }
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }
        if (! is_null($this->user)) {
            return $this->user;
        }
        $id = $this->session->get($this->getName());
        if (! is_null($id) && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }
        if (is_null($this->user) && ! is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);
            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());
                $this->fireLoginEvent($this->user, true);
            }
        }
        return $this->user;
    }
    protected function userFromRecaller($recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return;
        }
        $this->recallAttempted = true;
        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token()
        ));
        return $user;
    }
    protected function recaller()
    {
        if (is_null($this->request)) {
            return;
        }
        if ($recaller = $this->request->cookies->get($this->getRecallerName())) {
            return new Recaller($recaller);
        }
    }
    public function id()
    {
        if ($this->loggedOut) {
            return;
        }
        return $this->user()
                    ? $this->user()->getAuthIdentifier()
                    : $this->session->get($this->getName());
    }
    public function once(array $credentials = [])
    {
        $this->fireAttemptEvent($credentials);
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);
            return true;
        }
        return false;
    }
    public function onceUsingId($id)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);
            return $user;
        }
        return false;
    }
    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        return $this->hasValidCredentials($user, $credentials);
    }
    public function basic($field = 'email', $extraConditions = [])
    {
        if ($this->check()) {
            return;
        }
        if ($this->attemptBasic($this->getRequest(), $field, $extraConditions)) {
            return;
        }
        return $this->failedBasicResponse();
    }
    public function onceBasic($field = 'email', $extraConditions = [])
    {
        $credentials = $this->basicCredentials($this->getRequest(), $field);
        if (! $this->once(array_merge($credentials, $extraConditions))) {
            return $this->failedBasicResponse();
        }
    }
    protected function attemptBasic(Request $request, $field, $extraConditions = [])
    {
        if (! $request->getUser()) {
            return false;
        }
        return $this->attempt(array_merge(
            $this->basicCredentials($request, $field), $extraConditions
        ));
    }
    protected function basicCredentials(Request $request, $field)
    {
        return [$field => $request->getUser(), 'password' => $request->getPassword()];
    }
    protected function failedBasicResponse()
    {
        throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
    }
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->fireAttemptEvent($credentials, $remember);
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }
        $this->fireFailedEvent($user, $credentials);
        return false;
    }
    protected function hasValidCredentials($user, $credentials)
    {
        return ! is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }
    public function loginUsingId($id, $remember = false)
    {
        if (! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);
            return $user;
        }
        return false;
    }
    public function login(AuthenticatableContract $user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->queueRecallerCookie($user);
        }
        $this->fireLoginEvent($user, $remember);
        $this->setUser($user);
    }
    protected function updateSession($id)
    {
        $this->session->put($this->getName(), $id);
        $this->session->migrate(true);
    }
    protected function ensureRememberTokenIsSet(AuthenticatableContract $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }
    protected function queueRecallerCookie(AuthenticatableContract $user)
    {
        $this->getCookieJar()->queue($this->createRecaller(
            $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword()
        ));
    }
    protected function createRecaller($value)
    {
        return $this->getCookieJar()->forever($this->getRecallerName(), $value);
    }
    public function logout()
    {
        $user = $this->user();
        $this->clearUserDataFromStorage();
        if (! is_null($this->user)) {
            $this->cycleRememberToken($user);
        }
        if (isset($this->events)) {
            $this->events->dispatch(new Events\Logout($this->name, $user));
        }
        $this->user = null;
        $this->loggedOut = true;
    }
    protected function clearUserDataFromStorage()
    {
        $this->session->remove($this->getName());
        if (! is_null($this->recaller())) {
            $this->getCookieJar()->queue($this->getCookieJar()
                    ->forget($this->getRecallerName()));
        }
    }
    protected function cycleRememberToken(AuthenticatableContract $user)
    {
        $user->setRememberToken($token = Str::random(60));
        $this->provider->updateRememberToken($user, $token);
    }
    public function logoutOtherDevices($password, $attribute = 'password')
    {
        if (! $this->user()) {
            return;
        }
        $result = tap($this->user()->forceFill([
            $attribute => Hash::make($password),
        ]))->save();
        $this->queueRecallerCookie($this->user());
        return $result;
    }
    public function attempting($callback)
    {
        if (isset($this->events)) {
            $this->events->listen(Events\Attempting::class, $callback);
        }
    }
    protected function fireAttemptEvent(array $credentials, $remember = false)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Events\Attempting(
                $this->name, $credentials, $remember
            ));
        }
    }
    protected function fireLoginEvent($user, $remember = false)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Events\Login(
                $this->name, $user, $remember
            ));
        }
    }
    protected function fireAuthenticatedEvent($user)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Events\Authenticated(
                $this->name, $user
            ));
        }
    }
    protected function fireFailedEvent($user, array $credentials)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new Events\Failed(
                $this->name, $user, $credentials
            ));
        }
    }
    public function getLastAttempted()
    {
        return $this->lastAttempted;
    }
    public function getName()
    {
        return 'login_'.$this->name.'_'.sha1(static::class);
    }
    public function getRecallerName()
    {
        return 'remember_'.$this->name.'_'.sha1(static::class);
    }
    public function viaRemember()
    {
        return $this->viaRemember;
    }
    public function getCookieJar()
    {
        if (! isset($this->cookie)) {
            throw new RuntimeException('Cookie jar has not been set.');
        }
        return $this->cookie;
    }
    public function setCookieJar(CookieJar $cookie)
    {
        $this->cookie = $cookie;
    }
    public function getDispatcher()
    {
        return $this->events;
    }
    public function setDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }
    public function getSession()
    {
        return $this->session;
    }
    public function getUser()
    {
        return $this->user;
    }
    public function setUser(AuthenticatableContract $user)
    {
        $this->user = $user;
        $this->loggedOut = false;
        $this->fireAuthenticatedEvent($user);
        return $this;
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
}
