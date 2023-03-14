<?php
namespace Symfony\Component\HttpFoundation\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
class File extends \SplFileInfo
{
    public function __construct(string $path, bool $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new FileNotFoundException($path);
        }
        parent::__construct($path);
    }
    public function guessExtension()
    {
        $type = $this->getMimeType();
        $guesser = ExtensionGuesser::getInstance();
        return $guesser->guess($type);
    }
    public function getMimeType()
    {
        $guesser = MimeTypeGuesser::getInstance();
        return $guesser->guess($this->getPathname());
    }
    public function move($directory, $name = null)
    {
        $target = $this->getTargetFile($directory, $name);
        set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
        $renamed = rename($this->getPathname(), $target);
        restore_error_handler();
        if (!$renamed) {
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error)));
        }
        @chmod($target, 0666 & ~umask());
        return $target;
    }
    protected function getTargetFile($directory, $name = null)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }
        $target = rtrim($directory, '/\\').\DIRECTORY_SEPARATOR.(null === $name ? $this->getBasename() : $this->getName($name));
        return new self($target, false);
    }
    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);
        return $originalName;
    }
}
