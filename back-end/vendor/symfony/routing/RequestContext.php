<?php
namespace Symfony\Component\Routing;
use Symfony\Component\HttpFoundation\Request;
class RequestContext
{
    private $baseUrl;
    private $pathInfo;
    private $method;
    private $host;
    private $scheme;
    private $httpPort;
    private $httpsPort;
    private $queryString;
    private $parameters = [];
    public function __construct(string $baseUrl = '', string $method = 'GET', string $host = 'localhost', string $scheme = 'http', int $httpPort = 80, int $httpsPort = 443, string $path = '/', string $queryString = '')
    {
        $this->setBaseUrl($baseUrl);
        $this->setMethod($method);
        $this->setHost($host);
        $this->setScheme($scheme);
        $this->setHttpPort($httpPort);
        $this->setHttpsPort($httpsPort);
        $this->setPathInfo($path);
        $this->setQueryString($queryString);
    }
    public function fromRequest(Request $request)
    {
        $this->setBaseUrl($request->getBaseUrl());
        $this->setPathInfo($request->getPathInfo());
        $this->setMethod($request->getMethod());
        $this->setHost($request->getHost());
        $this->setScheme($request->getScheme());
        $this->setHttpPort($request->isSecure() ? $this->httpPort : $request->getPort());
        $this->setHttpsPort($request->isSecure() ? $request->getPort() : $this->httpsPort);
        $this->setQueryString($request->server->get('QUERY_STRING', ''));
        return $this;
    }
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }
    public function getPathInfo()
    {
        return $this->pathInfo;
    }
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;
        return $this;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function setHost($host)
    {
        $this->host = strtolower($host);
        return $this;
    }
    public function getScheme()
    {
        return $this->scheme;
    }
    public function setScheme($scheme)
    {
        $this->scheme = strtolower($scheme);
        return $this;
    }
    public function getHttpPort()
    {
        return $this->httpPort;
    }
    public function setHttpPort($httpPort)
    {
        $this->httpPort = (int) $httpPort;
        return $this;
    }
    public function getHttpsPort()
    {
        return $this->httpsPort;
    }
    public function setHttpsPort($httpsPort)
    {
        $this->httpsPort = (int) $httpsPort;
        return $this;
    }
    public function getQueryString()
    {
        return $this->queryString;
    }
    public function setQueryString($queryString)
    {
        $this->queryString = (string) $queryString;
        return $this;
    }
    public function getParameters()
    {
        return $this->parameters;
    }
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
    public function hasParameter($name)
    {
        return \array_key_exists($name, $this->parameters);
    }
    public function setParameter($name, $parameter)
    {
        $this->parameters[$name] = $parameter;
        return $this;
    }
}
