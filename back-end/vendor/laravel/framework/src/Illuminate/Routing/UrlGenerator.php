<?php
namespace Illuminate\Routing;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
class UrlGenerator implements UrlGeneratorContract
{
    use InteractsWithTime, Macroable;
    protected $routes;
    protected $request;
    protected $assetRoot;
    protected $forcedRoot;
    protected $forceScheme;
    protected $cachedRoot;
    protected $cachedSchema;
    protected $rootNamespace;
    protected $sessionResolver;
    protected $keyResolver;
    protected $formatHostUsing;
    protected $formatPathUsing;
    protected $routeGenerator;
    public function __construct(RouteCollection $routes, Request $request, $assetRoot = null)
    {
        $this->routes = $routes;
        $this->assetRoot = $assetRoot;
        $this->setRequest($request);
    }
    public function full()
    {
        return $this->request->fullUrl();
    }
    public function current()
    {
        return $this->to($this->request->getPathInfo());
    }
    public function previous($fallback = false)
    {
        $referrer = $this->request->headers->get('referer');
        $url = $referrer ? $this->to($referrer) : $this->getPreviousUrlFromSession();
        if ($url) {
            return $url;
        } elseif ($fallback) {
            return $this->to($fallback);
        }
        return $this->to('/');
    }
    protected function getPreviousUrlFromSession()
    {
        $session = $this->getSession();
        return $session ? $session->previousUrl() : null;
    }
    public function to($path, $extra = [], $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }
        $tail = implode('/', array_map(
            'rawurlencode', (array) $this->formatParameters($extra))
        );
        $root = $this->formatRoot($this->formatScheme($secure));
        [$path, $query] = $this->extractQueryString($path);
        return $this->format(
            $root, '/'.trim($path.'/'.$tail, '/')
        ).$query;
    }
    public function secure($path, $parameters = [])
    {
        return $this->to($path, $parameters, true);
    }
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }
        $root = $this->assetRoot
                    ? $this->assetRoot
                    : $this->formatRoot($this->formatScheme($secure));
        return $this->removeIndex($root).'/'.trim($path, '/');
    }
    public function secureAsset($path)
    {
        return $this->asset($path, true);
    }
    public function assetFrom($root, $path, $secure = null)
    {
        $root = $this->formatRoot($this->formatScheme($secure), $root);
        return $this->removeIndex($root).'/'.trim($path, '/');
    }
    protected function removeIndex($root)
    {
        $i = 'index.php';
        return Str::contains($root, $i) ? str_replace('/'.$i, '', $root) : $root;
    }
    public function formatScheme($secure = null)
    {
        if (! is_null($secure)) {
            return $secure ? 'https:
        }
        if (is_null($this->cachedSchema)) {
            $this->cachedSchema = $this->forceScheme ?: $this->request->getScheme().':
        }
        return $this->cachedSchema;
    }
    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true)
    {
        $parameters = $this->formatParameters($parameters);
        if ($expiration) {
            $parameters = $parameters + ['expires' => $this->availableAt($expiration)];
        }
        ksort($parameters);
        $key = call_user_func($this->keyResolver);
        return $this->route($name, $parameters + [
            'signature' => hash_hmac('sha256', $this->route($name, $parameters, $absolute), $key),
        ], $absolute);
    }
    public function temporarySignedRoute($name, $expiration, $parameters = [], $absolute = true)
    {
        return $this->signedRoute($name, $parameters, $expiration, $absolute);
    }
    public function hasValidSignature(Request $request, $absolute = true)
    {
        $url = $absolute ? $request->url() : '/'.$request->path();
        $original = rtrim($url.'?'.Arr::query(
            Arr::except($request->query(), 'signature')
        ), '?');
        $expires = $request->query('expires');
        $signature = hash_hmac('sha256', $original, call_user_func($this->keyResolver));
        return  hash_equals($signature, (string) $request->query('signature', '')) &&
               ! ($expires && Carbon::now()->getTimestamp() > $expires);
    }
    public function route($name, $parameters = [], $absolute = true)
    {
        if (! is_null($route = $this->routes->getByName($name))) {
            return $this->toRoute($route, $parameters, $absolute);
        }
        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }
    protected function toRoute($route, $parameters, $absolute)
    {
        return $this->routeUrl()->to(
            $route, $this->formatParameters($parameters), $absolute
        );
    }
    public function action($action, $parameters = [], $absolute = true)
    {
        if (is_null($route = $this->routes->getByAction($action = $this->formatAction($action)))) {
            throw new InvalidArgumentException("Action {$action} not defined.");
        }
        return $this->toRoute($route, $parameters, $absolute);
    }
    protected function formatAction($action)
    {
        if (is_array($action)) {
            $action = '\\'.implode('@', $action);
        }
        if ($this->rootNamespace && strpos($action, '\\') !== 0) {
            return $this->rootNamespace.'\\'.$action;
        } else {
            return trim($action, '\\');
        }
    }
    public function formatParameters($parameters)
    {
        $parameters = Arr::wrap($parameters);
        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $parameter->getRouteKey();
            }
        }
        return $parameters;
    }
    protected function extractQueryString($path)
    {
        if (($queryPosition = strpos($path, '?')) !== false) {
            return [
                substr($path, 0, $queryPosition),
                substr($path, $queryPosition),
            ];
        }
        return [$path, ''];
    }
    public function formatRoot($scheme, $root = null)
    {
        if (is_null($root)) {
            if (is_null($this->cachedRoot)) {
                $this->cachedRoot = $this->forcedRoot ?: $this->request->root();
            }
            $root = $this->cachedRoot;
        }
        $start = Str::startsWith($root, 'http:
        return preg_replace('~'.$start.'~', $scheme, $root, 1);
    }
    public function format($root, $path, $route = null)
    {
        $path = '/'.trim($path, '/');
        if ($this->formatHostUsing) {
            $root = call_user_func($this->formatHostUsing, $root, $route);
        }
        if ($this->formatPathUsing) {
            $path = call_user_func($this->formatPathUsing, $path, $route);
        }
        return trim($root.$path, '/');
    }
    public function isValidUrl($path)
    {
        if (! preg_match('~^(#|
            return filter_var($path, FILTER_VALIDATE_URL) !== false;
        }
        return true;
    }
    protected function routeUrl()
    {
        if (! $this->routeGenerator) {
            $this->routeGenerator = new RouteUrlGenerator($this, $this->request);
        }
        return $this->routeGenerator;
    }
    public function defaults(array $defaults)
    {
        $this->routeUrl()->defaults($defaults);
    }
    public function getDefaultParameters()
    {
        return $this->routeUrl()->defaultParameters;
    }
    public function forceScheme($scheme)
    {
        $this->cachedSchema = null;
        $this->forceScheme = $scheme.':
    }
    public function forceRootUrl($root)
    {
        $this->forcedRoot = rtrim($root, '/');
        $this->cachedRoot = null;
    }
    public function formatHostUsing(Closure $callback)
    {
        $this->formatHostUsing = $callback;
        return $this;
    }
    public function formatPathUsing(Closure $callback)
    {
        $this->formatPathUsing = $callback;
        return $this;
    }
    public function pathFormatter()
    {
        return $this->formatPathUsing ?: function ($path) {
            return $path;
        };
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->cachedRoot = null;
        $this->cachedSchema = null;
        $this->routeGenerator = null;
    }
    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
        return $this;
    }
    protected function getSession()
    {
        if ($this->sessionResolver) {
            return call_user_func($this->sessionResolver);
        }
    }
    public function setSessionResolver(callable $sessionResolver)
    {
        $this->sessionResolver = $sessionResolver;
        return $this;
    }
    public function setKeyResolver(callable $keyResolver)
    {
        $this->keyResolver = $keyResolver;
        return $this;
    }
    public function setRootControllerNamespace($rootNamespace)
    {
        $this->rootNamespace = $rootNamespace;
        return $this;
    }
}
