<?php
namespace Lcobucci\JWT\Signer\Rsa;
use Lcobucci\JWT\Signer\Rsa;
class Sha384 extends Rsa
{
    public function getAlgorithmId()
    {
        return 'RS384';
    }
    public function getAlgorithm()
    {
        return OPENSSL_ALGO_SHA384;
    }
}
