<?php
namespace Faker\Provider\fa_IR;
class Address extends \Faker\Provider\Address
{
    protected static $cityPrefix = array('استان');
    protected static $streetPrefix = array('خیابان');
    protected static $buildingNamePrefix = array('ساختمان');
    protected static $buildingNumberPrefix = array('پلاک', 'قطعه');
    protected static $postcodePrefix = array('کد پستی');
    protected static $cityName = array(
        "آذربایجان شرقی", "آذربایجان غربی", "اردبیل", "اصفهان", "البرز", "ایلام", "بوشهر",
        "تهران", "خراسان جنوبی", "خراسان رضوی", "خراسان شمالی", "خوزستان", "زنجان", "سمنان",
        "سیستان و بلوچستان", "فارس", "قزوین", "قم", "لرستان", "مازندران", "مرکزی", "هرمزگان",
        "همدان", "چهارمحال و بختیاری", "کردستان", "کرمان", "کرمانشاه", "کهگیلویه و بویراحمد",
        "گلستان", "گیلان", "یزد"
    );
    protected static $cityFormats = array(
        '{{cityName}}',
        '{{cityPrefix}} {{cityName}}',
    );
    protected static $streetNameFormats = array(
        '{{streetPrefix}} {{lastName}}'
    );
    protected static $streetAddressFormats = array(
        '{{streetName}} {{building}}'
    );
    protected static $addressFormats = array(
        '{{city}} {{streetAddress}} {{postcodePrefix}} {{postcode}}',
        '{{city}} {{streetAddress}}',
    );
    protected static $buildingFormat = array(
        '{{buildingNamePrefix}} {{firstName}} {{buildingNumberPrefix}} {{buildingNumber}}',
        '{{buildingNamePrefix}} {{firstName}}',
    );
    protected static $postcode = array('##########');
    protected static $country = array('ایران');
    public static function cityPrefix()
    {
        return static::randomElement(static::$cityPrefix);
    }
    public static function cityName()
    {
        return static::randomElement(static::$cityName);
    }
    public static function streetPrefix()
    {
        return static::randomElement(static::$streetPrefix);
    }
    public static function buildingNamePrefix()
    {
        return static::randomElement(static::$buildingNamePrefix);
    }
    public static function buildingNumberPrefix()
    {
        return static::randomElement(static::$buildingNumberPrefix);
    }
    public function building()
    {
        $format = static::randomElement(static::$buildingFormat);
        return $this->generator->parse($format);
    }
    public static function postcodePrefix()
    {
        return static::randomElement(static::$postcodePrefix);
    }
}
