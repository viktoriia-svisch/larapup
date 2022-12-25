<?php
namespace Illuminate\Foundation\Http\Middleware;
class ConvertEmptyStringsToNull extends TransformsRequest
{
    protected function transform($key, $value)
    {
        return is_string($value) && $value === '' ? null : $value;
    }
}
