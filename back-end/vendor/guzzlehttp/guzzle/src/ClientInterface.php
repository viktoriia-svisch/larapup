<?php
namespace GuzzleHttp;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
interface ClientInterface
{
    const VERSION = '6.3.3';
    public function send(RequestInterface $request, array $options = []);
    public function sendAsync(RequestInterface $request, array $options = []);
    public function request($method, $uri, array $options = []);
    public function requestAsync($method, $uri, array $options = []);
    public function getConfig($option = null);
}
