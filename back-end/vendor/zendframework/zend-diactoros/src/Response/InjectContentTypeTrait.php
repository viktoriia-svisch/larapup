<?php
namespace Zend\Diactoros\Response;
use function array_keys;
use function array_reduce;
use function strtolower;
trait InjectContentTypeTrait
{
    private function injectContentType($contentType, array $headers)
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        return $headers;
    }
}
