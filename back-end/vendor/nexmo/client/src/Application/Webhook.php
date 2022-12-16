<?php
namespace Nexmo\Application;
class Webhook
{
    const METHOD_POST = 'POST';
    const METHOD_GET  = 'GET';
    protected $method;
    protected $url;
    public function __construct($url, $method = self::METHOD_POST)
    {
        $this->url = $url;
        $this->method = $method;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function __toString()
    {
        return $this->getUrl();
    }
}
