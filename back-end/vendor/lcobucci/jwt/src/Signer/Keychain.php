<?php
namespace Lcobucci\JWT\Signer;
class Keychain
{
    public function getPrivateKey($key, $passphrase = null)
    {
        return new Key($key, $passphrase);
    }
    public function getPublicKey($certificate)
    {
        return new Key($certificate);
    }
}
