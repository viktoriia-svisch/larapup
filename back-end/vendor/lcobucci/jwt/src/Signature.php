<?php
namespace Lcobucci\JWT;
class Signature
{
    protected $hash;
    public function __construct($hash)
    {
        $this->hash = $hash;
    }
    public function verify(Signer $signer, $payload, $key)
    {
        return $signer->verify($this->hash, $payload, $key);
    }
    public function __toString()
    {
        return $this->hash;
    }
}
