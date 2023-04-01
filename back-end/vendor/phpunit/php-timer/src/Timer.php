<?php declare(strict_types=1);
namespace SebastianBergmann\Timer;
final class Timer
{
    private static $sizes = [
        'GB' => 1073741824,
        'MB' => 1048576,
        'KB' => 1024,
    ];
    private static $times = [
        'hour'   => 3600000,
        'minute' => 60000,
        'second' => 1000,
    ];
    private static $startTimes = [];
    public static function start(): void
    {
        self::$startTimes[] = \microtime(true);
    }
    public static function stop(): float
    {
        return \microtime(true) - \array_pop(self::$startTimes);
    }
    public static function bytesToString(int $bytes): string
    {
        foreach (self::$sizes as $unit => $value) {
            if ($bytes >= $value) {
                $size = \sprintf('%.2f', $bytes >= 1024 ? $bytes / $value : $bytes);
                return $size . ' ' . $unit;
            }
        }
        return $bytes . ' byte' . ($bytes !== 1 ? 's' : '');
    }
    public static function secondsToTimeString(float $time): string
    {
        $ms = \round($time * 1000);
        foreach (self::$times as $unit => $value) {
            if ($ms >= $value) {
                $time = \floor($ms / $value * 100.0) / 100.0;
                return $time . ' ' . ($time == 1 ? $unit : $unit . 's');
            }
        }
        return $ms . ' ms';
    }
    public static function timeSinceStartOfRequest(): string
    {
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $startOfRequest = $_SERVER['REQUEST_TIME_FLOAT'];
        } elseif (isset($_SERVER['REQUEST_TIME'])) {
            $startOfRequest = $_SERVER['REQUEST_TIME'];
        } else {
            throw new RuntimeException('Cannot determine time at which the request started');
        }
        return self::secondsToTimeString(\microtime(true) - $startOfRequest);
    }
    public static function resourceUsage(): string
    {
        return \sprintf(
            'Time: %s, Memory: %s',
            self::timeSinceStartOfRequest(),
            self::bytesToString(\memory_get_peak_usage(true))
        );
    }
}
