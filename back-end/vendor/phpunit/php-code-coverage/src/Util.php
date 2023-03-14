<?php
namespace SebastianBergmann\CodeCoverage;
final class Util
{
    public static function percent(float $a, float $b, bool $asString = false, bool $fixedWidth = false)
    {
        if ($asString && $b == 0) {
            return '';
        }
        $percent = 100;
        if ($b > 0) {
            $percent = ($a / $b) * 100;
        }
        if ($asString) {
            $format = $fixedWidth ? '%6.2F%%' : '%01.2F%%';
            return \sprintf($format, $percent);
        }
        return $percent;
    }
}
