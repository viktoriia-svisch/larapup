<?php
namespace Illuminate\Session;
use SessionHandlerInterface;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class EncryptedStore extends Store
{
    protected $encrypter;
    public function __construct($name, SessionHandlerInterface $handler, EncrypterContract $encrypter, $id = null)
    {
        $this->encrypter = $encrypter;
        parent::__construct($name, $handler, $id);
    }
    protected function prepareForUnserialize($data)
    {
        try {
            return $this->encrypter->decrypt($data);
        } catch (DecryptException $e) {
            return serialize([]);
        }
    }
    protected function prepareForStorage($data)
    {
        return $this->encrypter->encrypt($data);
    }
    public function getEncrypter()
    {
        return $this->encrypter;
    }
}
