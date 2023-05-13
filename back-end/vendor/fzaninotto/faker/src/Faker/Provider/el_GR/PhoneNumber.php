<?php
namespace Faker\Provider\el_GR;
class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $formats = array(
        '+30 2# ########',
        '+30 2## #######',
        '+30 2### ######',
        '+302#########',
        '+3069########',
        '+30 69 ########',
        '+30 69########',
        '+30 69# #######',
        '+30 69# ### ####',
        '+30 69# #### ###',
        '+30 69## ######',
        '+30 69## ## ## ##',
        '+30 69## ### ###',
        '2#########',
        '2## #######',
        '2### ######',
        '69########',
        '69# #######',
        '69# ### ####',
        '69# #### ###',
        '69## ######',
        '69## ## ## ##',
        '69## ### ###',
    );
    protected static $mobileFormats = array(
        '+3069########',
        '+30 69########',
        '+30 69# #######',
        '+30 69# ### ####',
        '+30 69# #### ###',
        '+30 69## ######',
        '+30 69## ## ## ##',
        '+30 69## ### ###',
        '69########',
        '69# #######',
        '69# ### ####',
        '69# #### ###',
        '69## ######',
        '69## ## ## ##',
        '69## ### ###',
    );
    public static function mobilePhoneNumber()
    {
        return static::numerify(static::randomElement(static::$mobileFormats));
    }
    protected static $tollFreeFormats = array(
        '+30 800#######',
        '+30 800 #######',
        '+30 800 ## #####',
        '+30 800 ### ####',
        '800#######',
        '800 #######',
        '800 ## #####',
        '800 ### ####',
    );
    public static function tollFreeNumber()
    {
        return static::numerify(static::randomElement(static::$tollFreeFormats));
    }
}
