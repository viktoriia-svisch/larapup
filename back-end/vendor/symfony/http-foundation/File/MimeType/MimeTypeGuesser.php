<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
class MimeTypeGuesser implements MimeTypeGuesserInterface
{
    private static $instance = null;
    protected $guessers = [];
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public static function reset()
    {
        self::$instance = null;
    }
    private function __construct()
    {
        $this->register(new FileBinaryMimeTypeGuesser());
        $this->register(new FileinfoMimeTypeGuesser());
    }
    public function register(MimeTypeGuesserInterface $guesser)
    {
        array_unshift($this->guessers, $guesser);
    }
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }
        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }
        foreach ($this->guessers as $guesser) {
            if (null !== $mimeType = $guesser->guess($path)) {
                return $mimeType;
            }
        }
        if (2 === \count($this->guessers) && !FileBinaryMimeTypeGuesser::isSupported() && !FileinfoMimeTypeGuesser::isSupported()) {
            throw new \LogicException('Unable to guess the mime type as no guessers are available (Did you enable the php_fileinfo extension?)');
        }
    }
}
