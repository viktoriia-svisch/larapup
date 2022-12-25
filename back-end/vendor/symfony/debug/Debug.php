<?php
namespace Symfony\Component\Debug;
class Debug
{
    private static $enabled = false;
    public static function enable($errorReportingLevel = E_ALL, $displayErrors = true)
    {
        if (static::$enabled) {
            return;
        }
        static::$enabled = true;
        if (null !== $errorReportingLevel) {
            error_reporting($errorReportingLevel);
        } else {
            error_reporting(E_ALL);
        }
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            ini_set('display_errors', 0);
            ExceptionHandler::register();
        } elseif ($displayErrors && (!filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN) || ini_get('error_log'))) {
            ini_set('display_errors', 1);
        }
        if ($displayErrors) {
            ErrorHandler::register(new ErrorHandler(new BufferingLogger()));
        } else {
            ErrorHandler::register()->throwAt(0, true);
        }
        DebugClassLoader::enable();
    }
}
