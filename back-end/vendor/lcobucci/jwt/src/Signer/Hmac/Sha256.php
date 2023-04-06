<?php
namespace Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Hmac;
class Sha256 extends Hmac
{
    public function getAlgorithmId()
    {
        return 'HS256';
    }
    public function getAlgorithm()
    {
        return 'sha256';
    }
}
