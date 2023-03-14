<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\Exception;
final class Json
{
    public static function prettify(string $json): string
    {
        $decodedJson = \json_decode($json, true);
        if (\json_last_error()) {
            throw new Exception(
                'Cannot prettify invalid json'
            );
        }
        return \json_encode($decodedJson, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
    }
    public static function canonicalize(string $json): array
    {
        $decodedJson = \json_decode($json);
        if (\json_last_error()) {
            return [true, null];
        }
        self::recursiveSort($decodedJson);
        $reencodedJson = \json_encode($decodedJson);
        return [false, $reencodedJson];
    }
    private static function recursiveSort(&$json): void
    {
        if (\is_array($json) === false) {
            if (\is_object($json) && \count((array) $json) > 0) {
                $json = (array) $json;
            } else {
                return;
            }
        }
        \ksort($json);
        foreach ($json as $key => &$value) {
            self::recursiveSort($value);
        }
    }
}
