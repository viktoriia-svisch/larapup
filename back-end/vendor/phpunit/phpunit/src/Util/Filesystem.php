<?php
namespace PHPUnit\Util;
final class Filesystem
{
    public static function classNameToFilename(string $className): string
    {
        return \str_replace(
            ['_', '\\'],
            \DIRECTORY_SEPARATOR,
            $className
        ) . '.php';
    }
    public static function createDirectory(string $directory): bool
    {
        return !(!\is_dir($directory) && !@\mkdir($directory, 0777, true) && !\is_dir($directory));
    }
}
