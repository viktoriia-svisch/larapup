<?php
namespace Lcobucci\JWT\Signer;
use Exception;
use InvalidArgumentException;
use SplFileObject;
final class Key
{
    private $content;
    private $passphrase;
    public function __construct($content, $passphrase = null)
    {
        $this->setContent($content);
        $this->passphrase = $passphrase;
    }
    private function setContent($content)
    {
        if (strpos($content, 'file:
            $content = $this->readFile($content);
        }
        $this->content = $content;
    }
    private function readFile($content)
    {
        try {
            $file    = new SplFileObject(substr($content, 7));
            $content = '';
            while (! $file->eof()) {
                $content .= $file->fgets();
            }
            return $content;
        } catch (Exception $exception) {
            throw new InvalidArgumentException('You must inform a valid key file', 0, $exception);
        }
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getPassphrase()
    {
        return $this->passphrase;
    }
}
