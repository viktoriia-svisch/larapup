<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
trait MakesHttpRequests
{
    protected $defaultHeaders = [];
    protected $serverVariables = [];
    protected $followRedirects = false;
    public function withHeaders(array $headers)
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);
        return $this;
    }
    public function withHeader(string $name, string $value)
    {
        $this->defaultHeaders[$name] = $value;
        return $this;
    }
    public function flushHeaders()
    {
        $this->defaultHeaders = [];
        return $this;
    }
    public function withServerVariables(array $server)
    {
        $this->serverVariables = $server;
        return $this;
    }
    public function withoutMiddleware($middleware = null)
    {
        if (is_null($middleware)) {
            $this->app->instance('middleware.disable', true);
            return $this;
        }
        foreach ((array) $middleware as $abstract) {
            $this->app->instance($abstract, new class {
                public function handle($request, $next)
                {
                    return $next($request);
                }
            });
        }
        return $this;
    }
    public function withMiddleware($middleware = null)
    {
        if (is_null($middleware)) {
            unset($this->app['middleware.disable']);
            return $this;
        }
        foreach ((array) $middleware as $abstract) {
            unset($this->app[$abstract]);
        }
        return $this;
    }
    public function followingRedirects()
    {
        $this->followRedirects = true;
        return $this;
    }
    public function from(string $url)
    {
        return $this->withHeader('referer', $url);
    }
    public function get($uri, array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call('GET', $uri, [], [], [], $server);
    }
    public function getJson($uri, array $headers = [])
    {
        return $this->json('GET', $uri, [], $headers);
    }
    public function post($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call('POST', $uri, $data, [], [], $server);
    }
    public function postJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('POST', $uri, $data, $headers);
    }
    public function put($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call('PUT', $uri, $data, [], [], $server);
    }
    public function putJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('PUT', $uri, $data, $headers);
    }
    public function patch($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call('PATCH', $uri, $data, [], [], $server);
    }
    public function patchJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }
    public function delete($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);
        return $this->call('DELETE', $uri, $data, [], [], $server);
    }
    public function deleteJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }
    public function json($method, $uri, array $data = [], array $headers = [])
    {
        $files = $this->extractFilesFromDataArray($data);
        $content = json_encode($data);
        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);
        return $this->call(
            $method, $uri, [], [], $files, $this->transformHeadersToServerVars($headers), $content
        );
    }
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $kernel = $this->app->make(HttpKernel::class);
        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));
        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri), $method, $parameters,
            $cookies, $files, array_replace($this->serverVariables, $server), $content
        );
        $response = $kernel->handle(
            $request = Request::createFromBase($symfonyRequest)
        );
        if ($this->followRedirects) {
            $response = $this->followRedirects($response);
        }
        $kernel->terminate($request, $response);
        return $this->createTestResponse($response);
    }
    protected function prepareUrlForRequest($uri)
    {
        if (Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }
        if (! Str::startsWith($uri, 'http')) {
            $uri = config('app.url').'/'.$uri;
        }
        return trim($uri, '/');
    }
    protected function transformHeadersToServerVars(array $headers)
    {
        return collect(array_merge($this->defaultHeaders, $headers))->mapWithKeys(function ($value, $name) {
            $name = strtr(strtoupper($name), '-', '_');
            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }
    protected function formatServerHeaderKey($name)
    {
        if (! Str::startsWith($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_'.$name;
        }
        return $name;
    }
    protected function extractFilesFromDataArray(&$data)
    {
        $files = [];
        foreach ($data as $key => $value) {
            if ($value instanceof SymfonyUploadedFile) {
                $files[$key] = $value;
                unset($data[$key]);
            }
            if (is_array($value)) {
                $files[$key] = $this->extractFilesFromDataArray($value);
                $data[$key] = $value;
            }
        }
        return $files;
    }
    protected function followRedirects($response)
    {
        while ($response->isRedirect()) {
            $response = $this->get($response->headers->get('Location'));
        }
        $this->followRedirects = false;
        return $response;
    }
    protected function createTestResponse($response)
    {
        return TestResponse::fromBaseResponse($response);
    }
}
