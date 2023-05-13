<?php
namespace Http\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
interface HttpClient
{
    public function sendRequest(RequestInterface $request);
}
