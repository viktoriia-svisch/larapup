<?php
namespace Lcobucci\JWT\Signer\Rsa;
use Lcobucci\JWT\Signer\Rsa;
class Sha512 extends Rsa
{
    public function getAlgorithmId()
    {
        return 'RS512';
    }
    public function getAlgorithm()
    {
        return OPENSSL_ALGO_SHA512;
    }
}
