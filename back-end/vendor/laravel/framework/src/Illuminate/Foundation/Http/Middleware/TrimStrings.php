<?php
namespace Illuminate\Foundation\Http\Middleware;
class TrimStrings extends TransformsRequest
{
    protected $except = [
    ];
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }
        return is_string($value) ? trim($value) : $value;
    }
}
