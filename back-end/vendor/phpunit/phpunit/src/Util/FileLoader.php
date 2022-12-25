<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\Exception;
final class FileLoader
{
    public static function checkAndLoad(string $filename): string
    {
        $includePathFilename = \stream_resolve_include_path($filename);
        $localFile           = __DIR__ . \DIRECTORY_SEPARATOR . $filename;
        $isReadable = @\fopen($includePathFilename, 'r') !== false;
        if (!$includePathFilename || !$isReadable || $includePathFilename === $localFile) {
            throw new Exception(
                \sprintf('Cannot open file "%s".' . "\n", $filename)
            );
        }
        self::load($includePathFilename);
        return $includePathFilename;
    }
    public static function load(string $filename): void
    {
        $oldVariableNames = \array_keys(\get_defined_vars());
        include_once $filename;
        $newVariables     = \get_defined_vars();
        $newVariableNames = \array_diff(\array_keys($newVariables), $oldVariableNames);
        foreach ($newVariableNames as $variableName) {
            if ($variableName !== 'oldVariableNames') {
                $GLOBALS[$variableName] = $newVariables[$variableName];
            }
        }
    }
}
