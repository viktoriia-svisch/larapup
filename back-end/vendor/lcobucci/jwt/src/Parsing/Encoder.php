<?php
namespace Lcobucci\JWT\Parsing;
use RuntimeException;
class Encoder
{
    public function jsonEncode($data)
    {
        $json = json_encode($data);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new RuntimeException('Error while encoding to JSON: ' . json_last_error_msg());
        }
        return $json;
    }
    public function base64UrlEncode($data)
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }
}
