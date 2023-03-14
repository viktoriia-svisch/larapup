<?php
namespace Nexmo\Entity;
interface EntityInterface
{
    public function getRequest();
    public function getRequestData($sent = true);
    public function getResponse();
    public function getResponseData();
    public function setResponse(\Psr\Http\Message\ResponseInterface $response);
    public function setRequest(\Psr\Http\Message\RequestInterface $request);
}
