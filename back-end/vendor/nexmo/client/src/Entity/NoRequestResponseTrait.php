<?php
namespace Nexmo\Entity;
trait NoRequestResponseTrait
{
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        throw new \RuntimeException(__CLASS__ . ' does not support request / response');
    }
    public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        throw new \RuntimeException(__CLASS__ . ' does not support request / response');
    }
    public function getRequest()
    {
        return null;
    }
    public function getResponse()
    {
        return null;
    }
}
