<?php
namespace Faker\Provider\en_AU;
class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $formats = array(
        '#### ####',
        '####-####',
        '####.####',
        '########',
        '0{{areaCode}} #### ####',
        '0{{areaCode}}-####-####',
        '0{{areaCode}}.####.####',
        '0{{areaCode}}########',
        '(0{{areaCode}}) #### ####',
        '(0{{areaCode}})-####-####',
        '(0{{areaCode}}).####.####',
        '(0{{areaCode}})########',
        '+61 {{areaCode}} #### ####',
        '+61-{{areaCode}}-####-####',
        '+61.{{areaCode}}.####.####',
        '+61{{areaCode}}########',
    );
    protected static $mobileFormats = array(
        '04## ### ###',
        '04##-###-###',
        '04##.###.###',
        '+61 4## ### ###',
        '+61-4##-###-###',
        '+61.4##.###.###',
    );
    protected static $areacodes = array(
        '2', '3', '7', '8'
    );
    public static function mobileNumber()
    {
        return static::numerify(static::randomElement(static::$mobileFormats));
    }
    public static function areaCode()
    {
        return static::numerify(static::randomElement(static::$areacodes));
    }
}
