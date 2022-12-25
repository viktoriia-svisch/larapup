<?php
namespace Nexmo;
use Nexmo\Client\Exception;
class ApiErrorHandler {
    public static function check($body, $statusCode) {
        $statusCodeType = (int) ($statusCode / 100);
        if ($statusCodeType == 2) {
            return;
        }
        $errorMessage = $body['title'];
        if (isset($body['detail']) && $body['detail']) {
            $errorMessage .= ': '.$body['detail'].'.';
        } else {
            $errorMessage .= '.';
        }
        $errorMessage .= ' See '.$body['type'].' for more information';
        if ($statusCodeType == 5) {
            throw new Exception\Server($errorMessage, $statusCode);
        }
        if (isset($body['invalid_parameters'])) {
            throw new Exception\Validation($errorMessage, $statusCode, null, $body['invalid_parameters']);
        }
        throw new Exception\Request($errorMessage, $statusCode);
    }
}
