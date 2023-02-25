<?php
class Singleton
{
    private static $uniqueInstance = null;
    public static function getInstance()
    {
        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new self;
        }
        return self::$uniqueInstance;
    }
    protected function __construct()
    {
    }
    private function __clone()
    {
    }
}
