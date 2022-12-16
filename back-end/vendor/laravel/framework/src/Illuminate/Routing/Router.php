<?php
namespace Illuminate\Routing;
use Closure;
use ArrayObject;
use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
class Router implements RegistrarContract, BindingRegistrar
{
    use Macroable {
        __call as macroCall;
    }
    protected $events;
    protected $container;
    protected $routes;
    protected $current;
    protected $currentRequest;
    protected $middleware = [];
    protected $middlewareGroups = [];
    public $middlewarePriority = [];
    protected $binders = [];
    protected $patterns = [];
    protected $groupStack = [];
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
    public function __construct(Dispatcher $events, Container $container = null)
    {
        $this->events = $events;
        $this->routes = new RouteCollection;
        $this->container = $container ?: new Container;
    }
    public function get($uri, $action = null)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }
    public function post($uri, $action = null)
    {
        return $this->addRoute('POST', $uri, $action);
    }
    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }
    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }
    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }
    public function options($uri, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }
    public function any($uri, $action = null)
    {
        return $this->addRoute(self::$verbs, $uri, $action);
    }
    public function fallback($action)
    {
        $placeholder = 'fallbackPlaceholder';
        return $this->addRoute(
            'GET', "{{$placeholder}}", $action
        )->where($placeholder, '.*')->fallback();
    }
    public function redirect($uri, $destination, $status = 302)
    {
        return $this->any($uri, '\Illuminate\Routing\RedirectController')
                ->defaults('destination', $destination)
                ->defaults('status', $status);
    }
    public function permanentRedirect($uri, $destination)
    {
        return $this->redirect($uri, $destination, 301);
    }
    public function view($uri, $view, $data = [])
    {
        return $this->match(['GET', 'HEAD'], $uri, '\Illuminate\Routing\ViewController')
                ->defaults('view', $view)
                ->defaults('data', $data);
    }
    public function match($methods, $uri, $action = null)
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }
    public function resources(array $resources, array $options = [])
    {
        foreach ($resources as $name => $controller) {
            $this->resource($name, $controller, $options);
        }
    }
    public function resource($name, $controller, array $options = [])
    {
        if ($this->container && $this->container->bound(ResourceRegistrar::class)) {
            $registrar = $this->container->make(ResourceRegistrar::class);
        } else {
            $registrar = new ResourceRegistrar($this);
        }
        return new PendingResourceRegistration(
            $registrar, $name, $controller, $options
        );
    }
    public function apiResources(array $resources, array $options = [])
    {
        foreach ($resources as $name => $controller) {
            $this->apiResource($name, $controller, $options);
        }
    }
    public function apiResource($name, $controller, array $options = [])
    {
        $only = ['index', 'show', 'store', 'update', 'destroy'];
        if (isset($options['except'])) {
            $only = array_diff($only, (array) $options['except']);
        }
        return $this->resource($name, $controller, array_merge([
            'only' => $only,
        ], $options));
    }
    public function group(array $attributes, $routes)
    {
        $this->updateGroupStack($attributes);
        $this->loadRoutes($routes);
        array_pop($this->groupStack);
    }
    protected function updateGroupStack(array $attributes)
    {
        if (! empty($this->groupStack)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }
        $this->groupStack[] = $attributes;
    }
    public function mergeWithLastGroup($new)
    {
        return RouteGroup::merge($new, end($this->groupStack));
    }
    protected function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            $router = $this;
            require $routes;
        }
    }
    public function getLastGroupPrefix()
    {
        if (! empty($this->groupStack)) {
            $last = end($this->groupStack);
            return $last['prefix'] ?? '';
        }
        return '';
    }
    public function addRoute($methods, $uri, $action)
    {
        return $this->routes->add($this->createRoute($methods, $uri, $action));
    }
    protected function createRoute($methods, $uri, $action)
    {
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }
        $route = $this->newRoute(
            $methods, $this->prefix($uri), $action
        );
        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }
        $this->addWhereClausesToRoute($route);
        return $route;
    }
    protected function actionReferencesController($action)
    {
        if (! $action instanceof Closure) {
            return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
        }
        return false;
    }
    protected function convertToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }
        if (! empty($this->groupStack)) {
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }
        $action['controller'] = $action['uses'];
        return $action;
    }
    protected function prependGroupNamespace($class)
    {
        $group = end($this->groupStack);
        return isset($group['namespace']) && strpos($class, '\\') !== 0
                ? $group['namespace'].'\\'.$class : $class;
    }
    protected function newRoute($methods, $uri, $action)
    {
        return (new Route($methods, $uri, $action))
                    ->setRouter($this)
                    ->setContainer($this->container);
    }
    protected function prefix($uri)
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }
    protected function addWhereClausesToRoute($route)
    {
        $route->where(array_merge(
            $this->patterns, $route->getAction()['where'] ?? []
        ));
        return $route;
    }
    protected function mergeGroupAttributesIntoRoute($route)
    {
        $route->setAction($this->mergeWithLastGroup($route->getAction()));
    }
    public function respondWithRoute($name)
    {
        $route = tap($this->routes->getByName($name))->bind($this->currentRequest);
        return $this->runRoute($this->currentRequest, $route);
    }
    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;
        return $this->dispatchToRoute($request);
    }
    public function dispatchToRoute(Request $request)
    {
        return $this->runRoute($request, $this->findRoute($request));
    }
    protected function findRoute($request)
    {
        $this->current = $route = $this->routes->match($request);
        $this->container->instance(Route::class, $route);
        return $route;
    }
    protected function runRoute(Request $request, Route $route)
    {
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        $this->events->dispatch(new Events\RouteMatched($route, $request));
        return $this->prepareResponse($request,
            $this->runRouteWithinStack($route, $request)
        );
    }
    protected function runRouteWithinStack(Route $route, Request $request)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
                                $this->container->make('middleware.disable') === true;
        $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);
        return (new Pipeline($this->container))
                        ->send($request)
                        ->through($middleware)
                        ->then(function ($request) use ($route) {
                            return $this->prepareResponse(
                                $request, $route->run()
                            );
                        });
    }
    public function gatherRouteMiddleware(Route $route)
    {
        $middleware = collect($route->gatherMiddleware())->map(function ($name) {
            return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
        })->flatten();
        return $this->sortMiddleware($middleware);
    }
    protected function sortMiddleware(Collection $middlewares)
    {
        return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
    }
    public function prepareResponse($request, $response)
    {
        return static::toResponse($request, $response);
    }
    public static function toResponse($request, $response)
    {
        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }
        if ($response instanceof PsrResponseInterface) {
            $response = (new HttpFoundationFactory)->createResponse($response);
        } elseif ($response instanceof Model && $response->wasRecentlyCreated) {
            $response = new JsonResponse($response, 201);
        } elseif (! $response instanceof SymfonyResponse &&
                   ($response instanceof Arrayable ||
                    $response instanceof Jsonable ||
                    $response instanceof ArrayObject ||
                    $response instanceof JsonSerializable ||
                    is_array($response))) {
            $response = new JsonResponse($response);
        } elseif (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        }
        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }
        return $response->prepare($request);
    }
    public function substituteBindings($route)
    {
        foreach ($route->parameters() as $key => $value) {
            if (isset($this->binders[$key])) {
                $route->setParameter($key, $this->performBinding($key, $value, $route));
            }
        }
        return $route;
    }
    public function substituteImplicitBindings($route)
    {
        ImplicitRouteBinding::resolveForRoute($this->container, $route);
    }
    protected function performBinding($key, $value, $route)
    {
        return call_user_func($this->binders[$key], $value, $route);
    }
    public function matched($callback)
    {
        $this->events->listen(Events\RouteMatched::class, $callback);
    }
    public function getMiddleware()
    {
        return $this->middleware;
    }
    public function aliasMiddleware($name, $class)
    {
        $this->middleware[$name] = $class;
        return $this;
    }
    public function hasMiddlewareGroup($name)
    {
        return array_key_exists($name, $this->middlewareGroups);
    }
    public function getMiddlewareGroups()
    {
        return $this->middlewareGroups;
    }
    public function middlewareGroup($name, array $middleware)
    {
        $this->middlewareGroups[$name] = $middleware;
        return $this;
    }
    public function prependMiddlewareToGroup($group, $middleware)
    {
        if (isset($this->middlewareGroups[$group]) && ! in_array($middleware, $this->middlewareGroups[$group])) {
            array_unshift($this->middlewareGroups[$group], $middleware);
        }
        return $this;
    }
    public function pushMiddlewareToGroup($group, $middleware)
    {
        if (! array_key_exists($group, $this->middlewareGroups)) {
            $this->middlewareGroups[$group] = [];
        }
        if (! in_array($middleware, $this->middlewareGroups[$group])) {
            $this->middlewareGroups[$group][] = $middleware;
        }
        return $this;
    }
    public function bind($key, $binder)
    {
        $this->binders[str_replace('-', '_', $key)] = RouteBinding::forCallback(
            $this->container, $binder
        );
    }
    public function model($key, $class, Closure $callback = null)
    {
        $this->bind($key, RouteBinding::forModel($this->container, $class, $callback));
    }
    public function getBindingCallback($key)
    {
        if (isset($this->binders[$key = str_replace('-', '_', $key)])) {
            return $this->binders[$key];
        }
    }
    public function getPatterns()
    {
        return $this->patterns;
    }
    public function pattern($key, $pattern)
    {
        $this->patterns[$key] = $pattern;
    }
    public function patterns($patterns)
    {
        foreach ($patterns as $key => $pattern) {
            $this->pattern($key, $pattern);
        }
    }
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }
    public function getGroupStack()
    {
        return $this->groupStack;
    }
    public function input($key, $default = null)
    {
        return $this->current()->parameter($key, $default);
    }
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }
    public function getCurrentRoute()
    {
        return $this->current();
    }
    public function current()
    {
        return $this->current;
    }
    public function has($name)
    {
        $names = is_array($name) ? $name : func_get_args();
        foreach ($names as $value) {
            if (! $this->routes->hasNamedRoute($value)) {
                return false;
            }
        }
        return true;
    }
    public function currentRouteName()
    {
        return $this->current() ? $this->current()->getName() : null;
    }
    public function is(...$patterns)
    {
        return $this->currentRouteNamed(...$patterns);
    }
    public function currentRouteNamed(...$patterns)
    {
        return $this->current() && $this->current()->named(...$patterns);
    }
    public function currentRouteAction()
    {
        if ($this->current()) {
            return $this->current()->getAction()['controller'] ?? null;
        }
    }
    public function uses(...$patterns)
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $this->currentRouteAction())) {
                return true;
            }
        }
        return false;
    }
    public function currentRouteUses($action)
    {
        return $this->currentRouteAction() == $action;
    }
    public function auth(array $options = [])
    {
        $this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
        $this->post('login', 'Auth\LoginController@login');
        $this->post('logout', 'Auth\LoginController@logout')->name('logout');
        if ($options['register'] ?? true) {
            $this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
            $this->post('register', 'Auth\RegisterController@register');
        }
        if ($options['reset'] ?? true) {
            $this->resetPassword();
        }
        if ($options['verify'] ?? false) {
            $this->emailVerification();
        }
    }
    public function resetPassword()
    {
        $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
        $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
        $this->post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    }
    public function emailVerification()
    {
        $this->get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
        $this->get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
        $this->get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
    }
    public function singularResourceParameters($singular = true)
    {
        ResourceRegistrar::singularParameters($singular);
    }
    public function resourceParameters(array $parameters = [])
    {
        ResourceRegistrar::setParameters($parameters);
    }
    public function resourceVerbs(array $verbs = [])
    {
        return ResourceRegistrar::verbs($verbs);
    }
    public function getRoutes()
    {
        return $this->routes;
    }
    public function setRoutes(RouteCollection $routes)
    {
        foreach ($routes as $route) {
            $route->setRouter($this)->setContainer($this->container);
        }
        $this->routes = $routes;
        $this->container->instance('routes', $this->routes);
    }
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        if ($method === 'middleware') {
            return (new RouteRegistrar($this))->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
        }
        return (new RouteRegistrar($this))->attribute($method, $parameters[0]);
    }
}
