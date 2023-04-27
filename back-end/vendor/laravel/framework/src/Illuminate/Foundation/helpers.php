<?php
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Container\Container;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
if (! function_exists('abort')) {
    function abort($code, $message = '', array $headers = [])
    {
        if ($code instanceof Response) {
            throw new HttpResponseException($code);
        } elseif ($code instanceof Responsable) {
            throw new HttpResponseException($code->toResponse(request()));
        }
        app()->abort($code, $message, $headers);
    }
}
if (! function_exists('abort_if')) {
    function abort_if($boolean, $code, $message = '', array $headers = [])
    {
        if ($boolean) {
            abort($code, $message, $headers);
        }
    }
}
if (! function_exists('abort_unless')) {
    function abort_unless($boolean, $code, $message = '', array $headers = [])
    {
        if (! $boolean) {
            abort($code, $message, $headers);
        }
    }
}
if (! function_exists('action')) {
    function action($name, $parameters = [], $absolute = true)
    {
        return app('url')->action($name, $parameters, $absolute);
    }
}
if (! function_exists('app')) {
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }
        return Container::getInstance()->make($abstract, $parameters);
    }
}
if (! function_exists('app_path')) {
    function app_path($path = '')
    {
        return app('path').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
if (! function_exists('asset')) {
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}
if (! function_exists('auth')) {
    function auth($guard = null)
    {
        if (is_null($guard)) {
            return app(AuthFactory::class);
        }
        return app(AuthFactory::class)->guard($guard);
    }
}
if (! function_exists('back')) {
    function back($status = 302, $headers = [], $fallback = false)
    {
        return app('redirect')->back($status, $headers, $fallback);
    }
}
if (! function_exists('base_path')) {
    function base_path($path = '')
    {
        return app()->basePath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
if (! function_exists('bcrypt')) {
    function bcrypt($value, $options = [])
    {
        return app('hash')->driver('bcrypt')->make($value, $options);
    }
}
if (! function_exists('broadcast')) {
    function broadcast($event = null)
    {
        return app(BroadcastFactory::class)->event($event);
    }
}
if (! function_exists('cache')) {
    function cache()
    {
        $arguments = func_get_args();
        if (empty($arguments)) {
            return app('cache');
        }
        if (is_string($arguments[0])) {
            return app('cache')->get(...$arguments);
        }
        if (! is_array($arguments[0])) {
            throw new Exception(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }
        if (! isset($arguments[1])) {
            throw new Exception(
                'You must specify an expiration time when setting a value in the cache.'
            );
        }
        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
    }
}
if (! function_exists('config')) {
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }
        if (is_array($key)) {
            return app('config')->set($key);
        }
        return app('config')->get($key, $default);
    }
}
if (! function_exists('config_path')) {
    function config_path($path = '')
    {
        return app()->make('path.config').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
if (! function_exists('cookie')) {
    function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true, $raw = false, $sameSite = null)
    {
        $cookie = app(CookieFactory::class);
        if (is_null($name)) {
            return $cookie;
        }
        return $cookie->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}
if (! function_exists('csrf_field')) {
    function csrf_field()
    {
        return new HtmlString('<input type="hidden" name="_token" value="'.csrf_token().'">');
    }
}
if (! function_exists('csrf_token')) {
    function csrf_token()
    {
        $session = app('session');
        if (isset($session)) {
            return $session->token();
        }
        throw new RuntimeException('Application session store not set.');
    }
}
if (! function_exists('database_path')) {
    function database_path($path = '')
    {
        return app()->databasePath($path);
    }
}
if (! function_exists('decrypt')) {
    function decrypt($value, $unserialize = true)
    {
        return app('encrypter')->decrypt($value, $unserialize);
    }
}
if (! function_exists('dispatch')) {
    function dispatch($job)
    {
        if ($job instanceof Closure) {
            $job = new CallQueuedClosure(new SerializableClosure($job));
        }
        return new PendingDispatch($job);
    }
}
if (! function_exists('dispatch_now')) {
    function dispatch_now($job, $handler = null)
    {
        return app(Dispatcher::class)->dispatchNow($job, $handler);
    }
}
if (! function_exists('elixir')) {
    function elixir($file, $buildDirectory = 'build')
    {
        static $manifest = [];
        static $manifestPath;
        if (empty($manifest) || $manifestPath !== $buildDirectory) {
            $path = public_path($buildDirectory.'/rev-manifest.json');
            if (file_exists($path)) {
                $manifest = json_decode(file_get_contents($path), true);
                $manifestPath = $buildDirectory;
            }
        }
        $file = ltrim($file, '/');
        if (isset($manifest[$file])) {
            return '/'.trim($buildDirectory.'/'.$manifest[$file], '/');
        }
        $unversioned = public_path($file);
        if (file_exists($unversioned)) {
            return '/'.trim($file, '/');
        }
        throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
    }
}
if (! function_exists('encrypt')) {
    function encrypt($value, $serialize = true)
    {
        return app('encrypter')->encrypt($value, $serialize);
    }
}
if (! function_exists('event')) {
    function event(...$args)
    {
        return app('events')->dispatch(...$args);
    }
}
if (! function_exists('factory')) {
    function factory()
    {
        $factory = app(EloquentFactory::class);
        $arguments = func_get_args();
        if (isset($arguments[1]) && is_string($arguments[1])) {
            return $factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? null);
        } elseif (isset($arguments[1])) {
            return $factory->of($arguments[0])->times($arguments[1]);
        }
        return $factory->of($arguments[0]);
    }
}
if (! function_exists('info')) {
    function info($message, $context = [])
    {
        app('log')->info($message, $context);
    }
}
if (! function_exists('logger')) {
    function logger($message = null, array $context = [])
    {
        if (is_null($message)) {
            return app('log');
        }
        return app('log')->debug($message, $context);
    }
}
if (! function_exists('logs')) {
    function logs($driver = null)
    {
        return $driver ? app('log')->driver($driver) : app('log');
    }
}
if (! function_exists('method_field')) {
    function method_field($method)
    {
        return new HtmlString('<input type="hidden" name="_method" value="'.$method.'">');
    }
}
if (! function_exists('mix')) {
    function mix($path, $manifestDirectory = '')
    {
        static $manifests = [];
        if (! Str::startsWith($path, '/')) {
            $path = "/{$path}";
        }
        if ($manifestDirectory && ! Str::startsWith($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }
        if (file_exists(public_path($manifestDirectory.'/hot'))) {
            $url = rtrim(file_get_contents(public_path($manifestDirectory.'/hot')));
            if (Str::startsWith($url, ['http:
                return new HtmlString(Str::after($url, ':').$path);
            }
            return new HtmlString("
        }
        $manifestPath = public_path($manifestDirectory.'/mix-manifest.json');
        if (! isset($manifests[$manifestPath])) {
            if (! file_exists($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }
            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }
        $manifest = $manifests[$manifestPath];
        if (! isset($manifest[$path])) {
            $exception = new Exception("Unable to locate Mix file: {$path}.");
            if (! app('config')->get('app.debug')) {
                report($exception);
                return $path;
            } else {
                throw $exception;
            }
        }
        return new HtmlString($manifestDirectory.$manifest[$path]);
    }
}
if (! function_exists('now')) {
    function now($tz = null)
    {
        return Carbon::now($tz);
    }
}
if (! function_exists('old')) {
    function old($key = null, $default = null)
    {
        return app('request')->old($key, $default);
    }
}
if (! function_exists('policy')) {
    function policy($class)
    {
        return app(Gate::class)->getPolicyFor($class);
    }
}
if (! function_exists('public_path')) {
    function public_path($path = '')
    {
        return app()->make('path.public').($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}
if (! function_exists('redirect')) {
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        if (is_null($to)) {
            return app('redirect');
        }
        return app('redirect')->to($to, $status, $headers, $secure);
    }
}
if (! function_exists('report')) {
    function report($exception)
    {
        if ($exception instanceof Throwable &&
            ! $exception instanceof Exception) {
            $exception = new FatalThrowableError($exception);
        }
        app(ExceptionHandler::class)->report($exception);
    }
}
if (! function_exists('request')) {
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }
        if (is_array($key)) {
            return app('request')->only($key);
        }
        $value = app('request')->__get($key);
        return is_null($value) ? value($default) : $value;
    }
}
if (! function_exists('rescue')) {
    function rescue(callable $callback, $rescue = null)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            report($e);
            return value($rescue);
        }
    }
}
if (! function_exists('resolve')) {
    function resolve($name)
    {
        return app($name);
    }
}
if (! function_exists('resource_path')) {
    function resource_path($path = '')
    {
        return app()->resourcePath($path);
    }
}
if (! function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        $factory = app(ResponseFactory::class);
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->make($content, $status, $headers);
    }
}
if (! function_exists('route')) {
    function route($name, $parameters = [], $absolute = true)
    {
        return app('url')->route($name, $parameters, $absolute);
    }
}
if (! function_exists('secure_asset')) {
    function secure_asset($path)
    {
        return asset($path, true);
    }
}
if (! function_exists('secure_url')) {
    function secure_url($path, $parameters = [])
    {
        return url($path, $parameters, true);
    }
}
if (! function_exists('session')) {
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }
        if (is_array($key)) {
            return app('session')->put($key);
        }
        return app('session')->get($key, $default);
    }
}
if (! function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return app('path.storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
if (! function_exists('today')) {
    function today($tz = null)
    {
        return Carbon::today($tz);
    }
}
if (! function_exists('trans')) {
    function trans($key = null, $replace = [], $locale = null)
    {
        if (is_null($key)) {
            return app('translator');
        }
        return app('translator')->trans($key, $replace, $locale);
    }
}
if (! function_exists('trans_choice')) {
    function trans_choice($key, $number, array $replace = [], $locale = null)
    {
        return app('translator')->transChoice($key, $number, $replace, $locale);
    }
}
if (! function_exists('__')) {
    function __($key, $replace = [], $locale = null)
    {
        return app('translator')->getFromJson($key, $replace, $locale);
    }
}
if (! function_exists('url')) {
    function url($path = null, $parameters = [], $secure = null)
    {
        if (is_null($path)) {
            return app(UrlGenerator::class);
        }
        return app(UrlGenerator::class)->to($path, $parameters, $secure);
    }
}
if (! function_exists('validator')) {
    function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $factory = app(ValidationFactory::class);
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->make($data, $rules, $messages, $customAttributes);
    }
}
if (! function_exists('view')) {
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);
        if (func_num_args() === 0) {
            return $factory;
        }
        return $factory->make($view, $data, $mergeData);
    }
}
