<?php
namespace Faker\Provider\en_HK;
class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $formats = array('2#######', '3#######', '5#######', '6#######', '9#######');
    protected static $mobileFormats = array('5#######', '6#######', '9#######');
    protected static $landlineFormats = array('2#######', '3#######');
    protected static $faxFormats = array('7#######');
    public static function mobileNumber()
    {
        return static::numerify(static::randomElement(static::$mobileFormats));
    }
    public static function landlineNumber()
    {
        return static::numerify(static::randomElement(static::$landlineFormats));
    }
    public static function faxNumber()
    {
        return static::numerify(static::randomElement(static::$faxFormats));
    }
}
