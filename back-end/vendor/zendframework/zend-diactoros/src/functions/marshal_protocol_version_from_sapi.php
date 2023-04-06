<?php
namespace Zend\Diactoros;
use UnexpectedValueException;
use function preg_match;
function marshalProtocolVersionFromSapi(array $server)
{
    if (! isset($server['SERVER_PROTOCOL'])) {
        return '1.1';
    }
    if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
        throw new UnexpectedValueException(sprintf(
            'Unrecognized protocol version (%s)',
            $server['SERVER_PROTOCOL']
        ));
    }
    return $matches['version'];
}
