<?php
namespace Zend\Diactoros;
use function array_key_exists;
use function strpos;
use function strtolower;
use function strtr;
use function substr;
function marshalHeadersFromSapi(array $server)
{
    $headers = [];
    foreach ($server as $key => $value) {
        if (strpos($key, 'REDIRECT_') === 0) {
            $key = substr($key, 9);
            if (array_key_exists($key, $server)) {
                continue;
            }
        }
        if ($value && strpos($key, 'HTTP_') === 0) {
            $name = strtr(strtolower(substr($key, 5)), '_', '-');
            $headers[$name] = $value;
            continue;
        }
        if ($value && strpos($key, 'CONTENT_') === 0) {
            $name = 'content-' . strtolower(substr($key, 8));
            $headers[$name] = $value;
            continue;
        }
    }
    return $headers;
}
