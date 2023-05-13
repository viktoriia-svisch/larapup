<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
class FileBinaryMimeTypeGuesser implements MimeTypeGuesserInterface
{
    private $cmd;
    public function __construct(string $cmd = 'file -b --mime %s 2>/dev/null')
    {
        $this->cmd = $cmd;
    }
    public static function isSupported()
    {
        static $supported = null;
        if (null !== $supported) {
            return $supported;
        }
        if ('\\' === \DIRECTORY_SEPARATOR || !\function_exists('passthru') || !\function_exists('escapeshellarg')) {
            return $supported = false;
        }
        ob_start();
        passthru('command -v file', $exitStatus);
        $binPath = trim(ob_get_clean());
        return $supported = 0 === $exitStatus && '' !== $binPath;
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
        ob_start();
        passthru(sprintf($this->cmd, escapeshellarg($path)), $return);
        if ($return > 0) {
            ob_end_clean();
            return;
        }
        $type = trim(ob_get_clean());
        if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-\.]+)#i', $type, $match)) {
            return;
        }
        return $match[1];
    }
}
