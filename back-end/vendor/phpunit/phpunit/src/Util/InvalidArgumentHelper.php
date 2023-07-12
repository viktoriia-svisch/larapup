<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\Exception;
final class InvalidArgumentHelper
{
    public static function factory(int $argument, string $type, $value = null): Exception
    {
        $stack = \debug_backtrace();
        return new Exception(
            \sprintf(
                'Argument #%d%sof %s::%s() must be a %s',
                $argument,
                $value !== null ? ' (' . \gettype($value) . '#' . $value . ')' : ' (No Value) ',
                $stack[1]['class'],
                $stack[1]['function'],
                $type
            )
        );
    }
}
