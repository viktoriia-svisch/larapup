<?php
namespace Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Hmac;
class Sha512 extends Hmac
{
    public function getAlgorithmId()
    {
        return 'HS512';
    }
    public function getAlgorithm()
    {
        return 'sha512';
    }
}
