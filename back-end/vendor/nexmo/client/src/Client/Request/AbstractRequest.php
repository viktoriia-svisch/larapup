<?php
namespace Nexmo\Client\Request;
abstract class AbstractRequest implements RequestInterface
{
    protected $params = array();
    public function getParams()
    {
        return array_filter($this->params, 'is_scalar');
    }
} 
