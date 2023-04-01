<?php
namespace Illuminate\Http;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Contracts\Support\MessageProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirectResponse;
class RedirectResponse extends BaseRedirectResponse
{
    use ForwardsCalls, ResponseTrait, Macroable {
        Macroable::__call as macroCall;
    }
    protected $request;
    protected $session;
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];
        foreach ($key as $k => $v) {
            $this->session->flash($k, $v);
        }
        return $this;
    }
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }
        return $this;
    }
    public function withInput(array $input = null)
    {
        $this->session->flashInput($this->removeFilesFromInput(
            ! is_null($input) ? $input : $this->request->input()
        ));
        return $this;
    }
    protected function removeFilesFromInput(array $input)
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->removeFilesFromInput($value);
            }
            if ($value instanceof SymfonyUploadedFile) {
                unset($input[$key]);
            }
        }
        return $input;
    }
    public function onlyInput()
    {
        return $this->withInput($this->request->only(func_get_args()));
    }
    public function exceptInput()
    {
        return $this->withInput($this->request->except(func_get_args()));
    }
    public function withErrors($provider, $key = 'default')
    {
        $value = $this->parseErrors($provider);
        $errors = $this->session->get('errors', new ViewErrorBag);
        if (! $errors instanceof ViewErrorBag) {
            $errors = new ViewErrorBag;
        }
        $this->session->flash(
            'errors', $errors->put($key, $value)
        );
        return $this;
    }
    protected function parseErrors($provider)
    {
        if ($provider instanceof MessageProvider) {
            return $provider->getMessageBag();
        }
        return new MessageBag((array) $provider);
    }
    public function getOriginalContent()
    {
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
    public function getSession()
    {
        return $this->session;
    }
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }
        static::throwBadMethodCallException($method);
    }
}
