<?php
namespace Illuminate\Routing\Exceptions;
use Exception;
class UrlGenerationException extends Exception
{
    public static function forMissingParameters($route)
    {
        return new static("Missing required parameters for [Route: {$route->getName()}] [URI: {$route->uri()}].");
    }
}
