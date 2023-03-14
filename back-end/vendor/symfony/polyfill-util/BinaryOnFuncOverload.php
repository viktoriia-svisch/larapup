<?php
namespace Symfony\Polyfill\Util;
class BinaryOnFuncOverload
{
    public static function strlen($s)
    {
        return mb_strlen($s, '8bit');
    }
    public static function strpos($haystack, $needle, $offset = 0)
    {
        return mb_strpos($haystack, $needle, $offset, '8bit');
    }
    public static function strrpos($haystack, $needle, $offset = 0)
    {
        return mb_strrpos($haystack, $needle, $offset, '8bit');
    }
    public static function substr($string, $start, $length = 2147483647)
    {
        return mb_substr($string, $start, $length, '8bit');
    }
    public static function stripos($s, $needle, $offset = 0)
    {
        return mb_stripos($s, $needle, $offset, '8bit');
    }
    public static function stristr($s, $needle, $part = false)
    {
        return mb_stristr($s, $needle, $part, '8bit');
    }
    public static function strrchr($s, $needle, $part = false)
    {
        return mb_strrchr($s, $needle, $part, '8bit');
    }
    public static function strripos($s, $needle, $offset = 0)
    {
        return mb_strripos($s, $needle, $offset, '8bit');
    }
    public static function strstr($s, $needle, $part = false)
    {
        return mb_strstr($s, $needle, $part, '8bit');
    }
}
