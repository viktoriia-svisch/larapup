<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use function get_class;
use function gettype;
use function in_array;
use function is_numeric;
use function is_object;
use function is_string;
use function ord;
use function preg_match;
use function sprintf;
use function strlen;
final class HeaderSecurity
{
    private function __construct()
    {
    }
    public static function filter($value)
    {
        $value  = (string) $value;
        $length = strlen($value);
        $string = '';
        for ($i = 0; $i < $length; $i += 1) {
            $ascii = ord($value[$i]);
            if ($ascii === 13) {
                $lf = ord($value[$i + 1]);
                $ws = ord($value[$i + 2]);
                if ($lf === 10 && in_array($ws, [9, 32], true)) {
                    $string .= $value[$i] . $value[$i + 1];
                    $i += 1;
                }
                continue;
            }
            if (($ascii < 32 && $ascii !== 9)
                || $ascii === 127
                || $ascii > 254
            ) {
                continue;
            }
            $string .= $value[$i];
        }
        return $string;
    }
    public static function isValid($value)
    {
        $value  = (string) $value;
        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)) {
            return false;
        }
        if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $value)) {
            return false;
        }
        return true;
    }
    public static function assertValid($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid header value type; must be a string or numeric; received %s',
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }
        if (! self::isValid($value)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not valid header value',
                $value
            ));
        }
    }
    public static function assertValidName($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid header name type; expected string; received %s',
                (is_object($name) ? get_class($name) : gettype($name))
            ));
        }
        if (! preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not valid header name',
                $name
            ));
        }
    }
}
