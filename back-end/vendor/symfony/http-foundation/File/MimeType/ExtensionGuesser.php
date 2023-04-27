<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
class ExtensionGuesser implements ExtensionGuesserInterface
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
    private function __construct()
    {
        $this->register(new MimeTypeExtensionGuesser());
    }
    public function register(ExtensionGuesserInterface $guesser)
    {
        array_unshift($this->guessers, $guesser);
    }
    public function guess($mimeType)
    {
        foreach ($this->guessers as $guesser) {
            if (null !== $extension = $guesser->guess($mimeType)) {
                return $extension;
            }
        }
    }
}
