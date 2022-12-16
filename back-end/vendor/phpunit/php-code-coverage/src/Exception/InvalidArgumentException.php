<?php
namespace SebastianBergmann\CodeCoverage;
final class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
    public static function create($argument, $type, $value = null): self
    {
        $stack = \debug_backtrace(0);
        return new self(
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
