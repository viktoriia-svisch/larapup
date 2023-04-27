<?php
namespace PHPUnit\Framework\Constraint;
class JsonMatchesErrorMessageProvider
{
    public static function determineJsonError(string $error, string $prefix = ''): ?string
    {
        switch ($error) {
            case \JSON_ERROR_NONE:
                return null;
            case \JSON_ERROR_DEPTH:
                return $prefix . 'Maximum stack depth exceeded';
            case \JSON_ERROR_STATE_MISMATCH:
                return $prefix . 'Underflow or the modes mismatch';
            case \JSON_ERROR_CTRL_CHAR:
                return $prefix . 'Unexpected control character found';
            case \JSON_ERROR_SYNTAX:
                return $prefix . 'Syntax error, malformed JSON';
            case \JSON_ERROR_UTF8:
                return $prefix . 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return $prefix . 'Unknown error';
        }
    }
    public static function translateTypeToPrefix(string $type): string
    {
        switch (\strtolower($type)) {
            case 'expected':
                $prefix = 'Expected value JSON decode error - ';
                break;
            case 'actual':
                $prefix = 'Actual value JSON decode error - ';
                break;
            default:
                $prefix = '';
                break;
        }
        return $prefix;
    }
}
