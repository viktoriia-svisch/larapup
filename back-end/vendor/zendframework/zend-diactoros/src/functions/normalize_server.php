<?php
namespace Zend\Diactoros;
use function is_callable;
function normalizeServer(array $server, callable $apacheRequestHeaderCallback = null)
{
    if (null === $apacheRequestHeaderCallback && is_callable('apache_request_headers')) {
        $apacheRequestHeaderCallback = 'apache_request_headers';
    }
    if (isset($server['HTTP_AUTHORIZATION'])
        || ! is_callable($apacheRequestHeaderCallback)
    ) {
        return $server;
    }
    $apacheRequestHeaders = $apacheRequestHeaderCallback();
    if (isset($apacheRequestHeaders['Authorization'])) {
        $server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['Authorization'];
        return $server;
    }
    if (isset($apacheRequestHeaders['authorization'])) {
        $server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['authorization'];
        return $server;
    }
    return $server;
}
