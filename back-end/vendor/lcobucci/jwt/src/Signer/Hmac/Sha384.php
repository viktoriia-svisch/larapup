<?php
namespace Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Hmac;
class Sha384 extends Hmac
{
    public function getAlgorithmId()
    {
        return 'HS384';
    }
    public function getAlgorithm()
    {
        return 'sha384';
    }
}
