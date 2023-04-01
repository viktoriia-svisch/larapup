<?php
namespace Nexmo\Entity;
trait Psr7Trait
{
    protected $request;
    protected $response;
    public function setResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->response = $response;
        $status = $response->getStatusCode();
        if($this instanceof JsonUnserializableInterface AND ((200 == $status) OR (201 == $status))){
            $this->jsonUnserialize($this->getResponseData());
        }
    }
    public function setRequest(\Psr\Http\Message\RequestInterface $request)
    {
        $this->request = $request;
    }
    public function getRequest()
    {
        return $this->request;
    }
    public function getResponse()
    {
        return $this->response;
    }
}
