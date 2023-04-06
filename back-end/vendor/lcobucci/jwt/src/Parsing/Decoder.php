<?php
namespace Lcobucci\JWT\Parsing;
use RuntimeException;
class Decoder
{
    public function jsonDecode($json)
    {
        $data = json_decode($json);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new RuntimeException('Error while decoding to JSON: ' . json_last_error_msg());
        }
        return $data;
    }
    public function base64UrlDecode($data)
    {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
