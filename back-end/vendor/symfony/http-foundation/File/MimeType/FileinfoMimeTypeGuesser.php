<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
class FileinfoMimeTypeGuesser implements MimeTypeGuesserInterface
{
    private $magicFile;
    public function __construct(string $magicFile = null)
    {
        $this->magicFile = $magicFile;
    }
    public static function isSupported()
    {
        return \function_exists('finfo_open');
    }
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }
        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }
        if (!self::isSupported()) {
            return;
        }
        if (!$finfo = new \finfo(FILEINFO_MIME_TYPE, $this->magicFile)) {
            return;
        }
        return $finfo->file($path);
    }
}
