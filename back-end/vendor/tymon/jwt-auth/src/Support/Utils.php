<?php
namespace Tymon\JWTAuth\Support;
use Carbon\Carbon;
class Utils
{
    public static function now()
    {
        return Carbon::now('UTC');
    }
    public static function timestamp($timestamp)
    {
        return Carbon::createFromTimestampUTC($timestamp)->timezone('UTC');
    }
    public static function isPast($timestamp, $leeway = 0)
    {
        $timestamp = static::timestamp($timestamp);
        return $leeway > 0
            ? $timestamp->addSeconds($leeway)->isPast()
            : $timestamp->isPast();
    }
    public static function isFuture($timestamp, $leeway = 0)
    {
        $timestamp = static::timestamp($timestamp);
        return $leeway > 0
            ? $timestamp->subSeconds($leeway)->isFuture()
            : $timestamp->isFuture();
    }
}
