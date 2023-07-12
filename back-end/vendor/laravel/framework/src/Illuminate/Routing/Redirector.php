<?php
namespace Illuminate\Routing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Session\Store as SessionStore;
class Redirector
{
    use Macroable;
    protected $generator;
    protected $session;
    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;
    }
    public function home($status = 302)
    {
        return $this->to($this->generator->route('home'), $status);
    }
    public function back($status = 302, $headers = [], $fallback = false)
    {
        return $this->createRedirect($this->generator->previous($fallback), $status, $headers);
    }
    public function refresh($status = 302, $headers = [])
    {
        return $this->to($this->generator->getRequest()->path(), $status, $headers);
    }
    public function guest($path, $status = 302, $headers = [], $secure = null)
    {
        $request = $this->generator->getRequest();
        $intended = $request->method() === 'GET' && $request->route() && ! $request->expectsJson()
                        ? $this->generator->full()
                        : $this->generator->previous();
        if ($intended) {
            $this->setIntendedUrl($intended);
        }
        return $this->to($path, $status, $headers, $secure);
    }
    public function intended($default = '/', $status = 302, $headers = [], $secure = null)
    {
        $path = $this->session->pull('url.intended', $default);
        return $this->to($path, $status, $headers, $secure);
    }
    public function setIntendedUrl($url)
    {
        $this->session->put('url.intended', $url);
    }
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->createRedirect($this->generator->to($path, [], $secure), $status, $headers);
    }
    public function away($path, $status = 302, $headers = [])
    {
        return $this->createRedirect($path, $status, $headers);
    }
    public function secure($path, $status = 302, $headers = [])
    {
        return $this->to($path, $status, $headers, true);
    }
    public function route($route, $parameters = [], $status = 302, $headers = [])
    {
        return $this->to($this->generator->route($route, $parameters), $status, $headers);
    }
    public function action($action, $parameters = [], $status = 302, $headers = [])
    {
        return $this->to($this->generator->action($action, $parameters), $status, $headers);
    }
    protected function createRedirect($path, $status, $headers)
    {
        return tap(new RedirectResponse($path, $status, $headers), function ($redirect) {
            if (isset($this->session)) {
                $redirect->setSession($this->session);
            }
            $redirect->setRequest($this->generator->getRequest());
        });
    }
    public function getUrlGenerator()
    {
        return $this->generator;
    }
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }
}
