<?php
namespace Zend\Diactoros;
use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function property_exists;
class Server
{
    private $callback;
    private $emitter;
    private $request;
    private $response;
    public function __construct(
        callable $callback,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $this->callback = $callback;
        $this->request  = $request;
        $this->response = $response;
    }
    public function __get($name)
    {
        if (! property_exists($this, $name)) {
            throw new OutOfBoundsException('Cannot retrieve arbitrary properties from server');
        }
        return $this->{$name};
    }
    public function setEmitter(Response\EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }
    public static function createServer(
        callable $callback,
        array $server,
        array $query,
        array $body,
        array $cookies,
        array $files
    ) {
        $request  = ServerRequestFactory::fromGlobals($server, $query, $body, $cookies, $files);
        $response = new Response();
        return new static($callback, $request, $response);
    }
    public static function createServerFromRequest(
        callable $callback,
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ) {
        if (! $response) {
            $response = new Response();
        }
        return new static($callback, $request, $response);
    }
    public function listen(callable $finalHandler = null)
    {
        $callback = $this->callback;
        $response = $callback($this->request, $this->response, $finalHandler);
        if (! $response instanceof ResponseInterface) {
            $response = $this->response;
        }
        $this->getEmitter()->emit($response);
    }
    private function getEmitter()
    {
        if (! $this->emitter) {
            $this->emitter = new Response\SapiEmitter();
        }
        return $this->emitter;
    }
}
