<?php
namespace Faker\Provider\ro_RO;
class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $normalFormats = array(
        'landline' => array(
            '021#######', 
            '023#######',
            '024#######',
            '025#######',
            '026#######',
            '027#######', 
            '031#######', 
            '033#######',
            '034#######',
            '035#######',
            '036#######',
            '037#######', 
        ),
        'mobile' => array(
            '07########',
        )
    );
    protected static $specialFormats = array(
        'toll-free' => array(
            '0800######',
            '0801######', 
            '0802######', 
            '0806######', 
            '0807######', 
            '0870######', 
        ),
        'premium-rate' => array(
            '0900######',
            '0903######', 
            '0906######', 
        )
    );
    public function phoneNumber()
    {
        $type = static::randomElement(array_keys(static::$normalFormats));
        $number = static::numerify(static::randomElement(static::$normalFormats[$type]));
        return $number;
    }
    public static function tollFreePhoneNumber()
    {
        $number = static::numerify(static::randomElement(static::$specialFormats['toll-free']));
        return $number;
    }
    public static function premiumRatePhoneNumber()
    {
        $number = static::numerify(static::randomElement(static::$specialFormats['premium-rate']));
        return $number;
    }
}
