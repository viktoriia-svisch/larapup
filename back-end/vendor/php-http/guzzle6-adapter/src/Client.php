<?php
namespace Http\Adapter\Guzzle6;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
class Client implements HttpClient, HttpAsyncClient
{
    private $client;
    public function __construct(ClientInterface $client = null)
    {
        if (!$client) {
            $client = static::buildClient();
        }
        $this->client = $client;
    }
    public static function createWithConfig(array $config)
    {
        return new self(static::buildClient($config));
    }
    public function sendRequest(RequestInterface $request)
    {
        $promise = $this->sendAsyncRequest($request);
        return $promise->wait();
    }
    public function sendAsyncRequest(RequestInterface $request)
    {
        $promise = $this->client->sendAsync($request);
        return new Promise($promise, $request);
    }
    private static function buildClient(array $config = [])
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);
        return new GuzzleClient($config);
    }
}
