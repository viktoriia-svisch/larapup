<?php
namespace Symfony\Component\HttpFoundation;
class ApacheRequest extends Request
{
    protected function prepareRequestUri()
    {
        return $this->server->get('REQUEST_URI');
    }
    protected function prepareBaseUrl()
    {
        $baseUrl = $this->server->get('SCRIPT_NAME');
        if (false === strpos($this->server->get('REQUEST_URI'), $baseUrl)) {
            return rtrim(\dirname($baseUrl), '/\\');
        }
        return $baseUrl;
    }
}
