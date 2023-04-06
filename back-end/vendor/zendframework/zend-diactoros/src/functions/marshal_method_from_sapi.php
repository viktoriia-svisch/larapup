<?php
namespace Zend\Diactoros;
function marshalMethodFromSapi(array $server)
{
    return isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
}
