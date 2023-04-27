<?php
namespace Composer\XdebugHandler;
class Process
{
    public static function addColorOption(array $args, $colorOption)
    {
        if (!$colorOption
            || in_array($colorOption, $args)
            || !preg_match('/^--([a-z]+$)|(^--[a-z]+=)/', $colorOption, $matches)) {
            return $args;
        }
        if (isset($matches[2])) {
            if (false !== ($index = array_search($matches[2].'auto', $args))) {
                $args[$index] = $colorOption;
                return $args;
            } elseif (preg_grep('/^'.$matches[2].'/', $args)) {
                return $args;
            }
        } elseif (in_array('--no-'.$matches[1], $args)) {
            return $args;
        }
        if (false !== ($index = array_search('--', $args))) {
            array_splice($args, $index, 0, $colorOption);
        } else {
            $args[] = $colorOption;
        }
        return $args;
    }
    public static function escape($arg, $meta = true, $module = false)
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return "'".str_replace("'", "'\\''", $arg)."'";
        }
        $quote = strpbrk($arg, " \t") !== false || $arg === '';
        $arg = preg_replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes);
        if ($meta) {
            $meta = $dquotes || preg_match('/%[^%]+%/', $arg);
            if (!$meta) {
                $quote = $quote || strpbrk($arg, '^&|<>()') !== false;
            } elseif ($module && !$dquotes && $quote) {
                $meta = false;
            }
        }
        if ($quote) {
            $arg = '"'.preg_replace('/(\\\\*)$/', '$1$1', $arg).'"';
        }
        if ($meta) {
            $arg = preg_replace('/(["^&|<>()%])/', '^$1', $arg);
        }
        return $arg;
    }
    public static function supportsColor($output)
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return (function_exists('sapi_windows_vt100_support')
                && sapi_windows_vt100_support($output))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }
        if (function_exists('stream_isatty')) {
            return stream_isatty($output);
        } elseif (function_exists('posix_isatty')) {
            return posix_isatty($output);
        }
        $stat = fstat($output);
        return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
    }
    public static function setEnv($name, $value = false)
    {
        $unset = false === $value;
        if (!putenv($unset ? $name : $name.'='.$value)) {
            return false;
        }
        if ($unset) {
            unset($_SERVER[$name]);
        } else {
            $_SERVER[$name] = $value;
        }
        return true;
    }
}
