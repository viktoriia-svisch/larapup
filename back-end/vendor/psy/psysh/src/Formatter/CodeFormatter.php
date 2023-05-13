<?php
namespace Psy\Formatter;
use JakubOnderka\PhpConsoleHighlighter\Highlighter;
use Psy\Configuration;
use Psy\ConsoleColorFactory;
use Psy\Exception\RuntimeException;
class CodeFormatter implements Formatter
{
    public static function format(\Reflector $reflector, $colorMode = null)
    {
        if (!self::isReflectable($reflector)) {
            throw new RuntimeException('Source code unavailable');
        }
        $colorMode = $colorMode ?: Configuration::COLOR_MODE_AUTO;
        if ($fileName = $reflector->getFileName()) {
            if (!\is_file($fileName)) {
                throw new RuntimeException('Source code unavailable');
            }
            $file  = \file_get_contents($fileName);
            $start = $reflector->getStartLine();
            $end   = $reflector->getEndLine() - $start;
            $factory     = new ConsoleColorFactory($colorMode);
            $colors      = $factory->getConsoleColor();
            $highlighter = new Highlighter($colors);
            return $highlighter->getCodeSnippet($file, $start, 0, $end);
        } else {
            throw new RuntimeException('Source code unavailable');
        }
    }
    private static function isReflectable(\Reflector $reflector)
    {
        return $reflector instanceof \ReflectionClass ||
            $reflector instanceof \ReflectionFunctionAbstract;
    }
}
