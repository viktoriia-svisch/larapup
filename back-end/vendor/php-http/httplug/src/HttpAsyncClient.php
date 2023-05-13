<?php
namespace Http\Client;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
interface HttpAsyncClient
{
    public function sendAsyncRequest(RequestInterface $request);
}
