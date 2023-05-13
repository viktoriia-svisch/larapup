<?php
namespace Lcobucci\JWT\Signer\Rsa;
use Lcobucci\JWT\Signer\Rsa;
class Sha256 extends Rsa
{
    public function getAlgorithmId()
    {
        return 'RS256';
    }
    public function getAlgorithm()
    {
        return OPENSSL_ALGO_SHA256;
    }
}
