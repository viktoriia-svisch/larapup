<?php
namespace Faker\Provider\kk_KZ;
use \Faker\Provider\DateTime;
class Person extends \Faker\Provider\Person
{
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;
    const CENTURY_19TH = 0;
    const CENTURY_20TH = 1;
    const CENTURY_21ST = 2;
    const MALE_CENTURY_19TH = 1;
    const MALE_CENTURY_20TH = 3;
    const MALE_CENTURY_21ST = 5;
    const FEMALE_CENTURY_19TH = 2;
    const FEMALE_CENTURY_20TH = 4;
    const FEMALE_CENTURY_21ST = 6;
    public static $firstSequenceBitWeights = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
    public static $secondSequenceBitWeights = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 1, 2);
    public static $genderCenturyMap = array(
        self::GENDER_MALE   => array(
            self::CENTURY_19TH => self::MALE_CENTURY_19TH,
            self::CENTURY_20TH => self::MALE_CENTURY_20TH,
            self::CENTURY_21ST => self::MALE_CENTURY_21ST,
        ),
        self::GENDER_FEMALE => array(
            self::CENTURY_19TH => self::FEMALE_CENTURY_19TH,
            self::CENTURY_20TH => self::FEMALE_CENTURY_20TH,
            self::CENTURY_21ST => self::FEMALE_CENTURY_21ST,
        ),
    );
    protected static $maleNameFormats = array(
        '{{lastName}}ұлы {{firstNameMale}}',
    );
    protected static $femaleNameFormats = array(
        '{{lastName}}қызы {{firstNameFemale}}',
    );
    protected static $firstNameMale = array(
        'Аылғазы',
        'Әбдіқадыр',
        'Бабағожа',
        'Ғайса',
        'Дәмен',
        'Егізбек',
        'Жазылбек',
        'Зұлпықар',
        'Игісін',
        'Кәдіржан',
        'Қадырқан',
        'Латиф',
        'Мағаз',
        'Нармағамбет',
        'Оңалбай',
        'Өндіріс',
        'Пердебек',
        'Рақат',
        'Сағындық',
        'Танабай',
        'Уайыс',
        'Ұйықбай',
        'Үрімбай',
        'Файзрахман',
        'Хангелді',
        'Шаттық',
        'Ыстамбақы',
        'Ібни',
    );
    protected static $firstNameFemale = array(
        'Асылтас',
        'Әужа',
        'Бүлдіршін',
        'Гүлшаш',
        'Ғафура',
        'Ділдә',
        'Еркежан',
        'Жібек',
        'Зылиқа',
        'Ирада',
        'Күнсұлу',
        'Қырмызы',
        'Ләтипа',
        'Мүштәри',
        'Нұршара',
        'Орынша',
        'Өрзия',
        'Перизат',
        'Рухия',
        'Сындыбала',
        'Тұрсынай',
        'Уәсима',
        'Ұрқия',
        'Үрия',
        'Фируза',
        'Хафиза',
        'Шырынгүл',
        'Ырысты',
        'Іңкәр',
    );
    protected static $lastName = array(
        'Адырбай',
        'Әжібай',
        'Байбөрі',
        'Ғизат',
        'Ділдабек',
        'Ешмұхамбет',
        'Жігер',
        'Зікірия',
        'Иса',
        'Кунту',
        'Қыдыр',
        'Лұқпан',
        'Мышырбай',
        'Нысынбай',
        'Ошақбай',
        'Өтетілеу',
        'Пірәлі',
        'Рүстем',
        'Сырмұхамбет',
        'Тілеміс',
        'Уәлі',
        'Ұлықбек',
        'Үстем',
        'Фахир',
        'Хұсайын',
        'Шілдебай',
        'Ыстамбақы',
        'Ісмет',
    );
    private static function getCenturyByYear($year)
    {
        if ($year >= 2000 && $year <= DateTime::year()) {
            return self::CENTURY_21ST;
        } elseif ($year >= 1900) {
            return self::CENTURY_20TH;
        } elseif ($year >= 1800) {
            return self::CENTURY_19TH;
        }
    }
    public static function individualIdentificationNumber(\DateTime $birthDate = null, $gender = self::GENDER_MALE)
    {
        if (!$birthDate) {
            $birthDate = DateTime::dateTimeBetween();
        }
        do {
            $population = mt_rand(1000, 2000);
            $century = self::getCenturyByYear((int) $birthDate->format('Y'));
            $iin = $birthDate->format('ymd');
            $iin .= (string) self::$genderCenturyMap[$gender][$century];
            $iin .= (string) $population;
            $checksum = self::checkSum($iin);
        } while ($checksum === 10);
        return $iin . (string) $checksum;
    }
    public static function checkSum($iinValue)
    {
        $controlDigit = self::getControlDigit($iinValue, self::$firstSequenceBitWeights);
        if ($controlDigit === 10) {
            return self::getControlDigit($iinValue, self::$secondSequenceBitWeights);
        }
        return $controlDigit;
    }
    protected static function getControlDigit($iinValue, $sequence)
    {
        $sum = 0;
        for ($i = 0; $i <= 10; $i++) {
            $sum += (int) $iinValue[$i] * $sequence[$i];
        }
        return $sum % 11;
    }
}
