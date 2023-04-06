<?php
namespace Monolog\Handler;
use Monolog\Formatter\LineFormatter;
class BrowserConsoleHandler extends AbstractProcessingHandler
{
    protected static $initialized = false;
    protected static $records = array();
    protected function getDefaultFormatter()
    {
        return new LineFormatter('[[%channel%]]{macro: autolabel} [[%level_name%]]{font-weight: bold} %message%');
    }
    protected function write(array $record)
    {
        static::$records[] = $record;
        if (!static::$initialized) {
            static::$initialized = true;
            $this->registerShutdownFunction();
        }
    }
    public static function send()
    {
        $format = static::getResponseFormat();
        if ($format === 'unknown') {
            return;
        }
        if (count(static::$records)) {
            if ($format === 'html') {
                static::writeOutput('<script>' . static::generateScript() . '</script>');
            } elseif ($format === 'js') {
                static::writeOutput(static::generateScript());
            }
            static::resetStatic();
        }
    }
    public function close()
    {
        self::resetStatic();
    }
    public function reset()
    {
        self::resetStatic();
    }
    public static function resetStatic()
    {
        static::$records = array();
    }
    protected function registerShutdownFunction()
    {
        if (PHP_SAPI !== 'cli') {
            register_shutdown_function(array('Monolog\Handler\BrowserConsoleHandler', 'send'));
        }
    }
    protected static function writeOutput($str)
    {
        echo $str;
    }
    protected static function getResponseFormat()
    {
        foreach (headers_list() as $header) {
            if (stripos($header, 'content-type:') === 0) {
                if (stripos($header, 'application/javascript') !== false || stripos($header, 'text/javascript') !== false) {
                    return 'js';
                }
                if (stripos($header, 'text/html') === false) {
                    return 'unknown';
                }
                break;
            }
        }
        return 'html';
    }
    private static function generateScript()
    {
        $script = array();
        foreach (static::$records as $record) {
            $context = static::dump('Context', $record['context']);
            $extra = static::dump('Extra', $record['extra']);
            if (empty($context) && empty($extra)) {
                $script[] = static::call_array('log', static::handleStyles($record['formatted']));
            } else {
                $script = array_merge($script,
                    array(static::call_array('groupCollapsed', static::handleStyles($record['formatted']))),
                    $context,
                    $extra,
                    array(static::call('groupEnd'))
                );
            }
        }
        return "(function (c) {if (c && c.groupCollapsed) {\n" . implode("\n", $script) . "\n}})(console);";
    }
    private static function handleStyles($formatted)
    {
        $args = array(static::quote('font-weight: normal'));
        $format = '%c' . $formatted;
        preg_match_all('/\[\[(.*?)\]\]\{([^}]*)\}/s', $format, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach (array_reverse($matches) as $match) {
            $args[] = static::quote(static::handleCustomStyles($match[2][0], $match[1][0]));
            $args[] = '"font-weight: normal"';
            $pos = $match[0][1];
            $format = substr($format, 0, $pos) . '%c' . $match[1][0] . '%c' . substr($format, $pos + strlen($match[0][0]));
        }
        array_unshift($args, static::quote($format));
        return $args;
    }
    private static function handleCustomStyles($style, $string)
    {
        static $colors = array('blue', 'green', 'red', 'magenta', 'orange', 'black', 'grey');
        static $labels = array();
        return preg_replace_callback('/macro\s*:(.*?)(?:;|$)/', function ($m) use ($string, &$colors, &$labels) {
            if (trim($m[1]) === 'autolabel') {
                if (!isset($labels[$string])) {
                    $labels[$string] = $colors[count($labels) % count($colors)];
                }
                $color = $labels[$string];
                return "background-color: $color; color: white; border-radius: 3px; padding: 0 2px 0 2px";
            }
            return $m[1];
        }, $style);
    }
    private static function dump($title, array $dict)
    {
        $script = array();
        $dict = array_filter($dict);
        if (empty($dict)) {
            return $script;
        }
        $script[] = static::call('log', static::quote('%c%s'), static::quote('font-weight: bold'), static::quote($title));
        foreach ($dict as $key => $value) {
            $value = json_encode($value);
            if (empty($value)) {
                $value = static::quote('');
            }
            $script[] = static::call('log', static::quote('%s: %o'), static::quote($key), $value);
        }
        return $script;
    }
    private static function quote($arg)
    {
        return '"' . addcslashes($arg, "\"\n\\") . '"';
    }
    private static function call()
    {
        $args = func_get_args();
        $method = array_shift($args);
        return static::call_array($method, $args);
    }
    private static function call_array($method, array $args)
    {
        return 'c.' . $method . '(' . implode(', ', $args) . ');';
    }
}
