<?php
class PHP_Token_Stream_CachingFactory
{
    protected static $cache = [];
    public static function get($filename)
    {
        if (!isset(self::$cache[$filename])) {
            self::$cache[$filename] = new PHP_Token_Stream($filename);
        }
        return self::$cache[$filename];
    }
    public static function clear($filename = null)
    {
        if (is_string($filename)) {
            unset(self::$cache[$filename]);
        } else {
            self::$cache = [];
        }
    }
}
