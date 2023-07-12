<?php
abstract class Swift
{
    const VERSION = '6.1.3';
    public static $initialized = false;
    public static $inits = [];
    public static function init($callable)
    {
        self::$inits[] = $callable;
    }
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'Swift_')) {
            return;
        }
        $path = __DIR__.'/'.str_replace('_', '/', $class).'.php';
        if (!file_exists($path)) {
            return;
        }
        require $path;
        if (self::$inits && !self::$initialized) {
            self::$initialized = true;
            foreach (self::$inits as $init) {
                call_user_func($init);
            }
        }
    }
    public static function registerAutoload($callable = null)
    {
        if (null !== $callable) {
            self::$inits[] = $callable;
        }
        spl_autoload_register(['Swift', 'autoload']);
    }
}
