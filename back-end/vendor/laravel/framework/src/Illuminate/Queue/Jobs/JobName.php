<?php
namespace Illuminate\Queue\Jobs;
use Illuminate\Support\Str;
class JobName
{
    public static function parse($job)
    {
        return Str::parseCallback($job, 'fire');
    }
    public static function resolve($name, $payload)
    {
        if (! empty($payload['displayName'])) {
            return $payload['displayName'];
        }
        return $name;
    }
}
