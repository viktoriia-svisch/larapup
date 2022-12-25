<?php declare(strict_types=1);
namespace SebastianBergmann\Diff\Utils;
final class FileUtils
{
    public static function getFileContent(string $file): string
    {
        $content = @\file_get_contents($file);
        if (false === $content) {
            $error = \error_get_last();
            throw new \RuntimeException(\sprintf(
                'Failed to read content of file "%s".%s',
                $file,
                $error ? ' ' . $error['message'] : ''
            ));
        }
        return $content;
    }
}
