<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
function createUploadedFile(array $spec)
{
    if (! isset($spec['tmp_name'])
        || ! isset($spec['size'])
        || ! isset($spec['error'])
    ) {
        throw new InvalidArgumentException(sprintf(
            '$spec provided to %s MUST contain each of the keys "tmp_name",'
            . ' "size", and "error"; one or more were missing',
            __FUNCTION__
        ));
    }
    return new UploadedFile(
        $spec['tmp_name'],
        $spec['size'],
        $spec['error'],
        isset($spec['name']) ? $spec['name'] : null,
        isset($spec['type']) ? $spec['type'] : null
    );
}
