<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
use Lcobucci\JWT\Signer\Ecdsa;
class Sha256 extends Ecdsa
{
    public function getAlgorithmId()
    {
        return 'ES256';
    }
    public function getAlgorithm()
    {
        return 'sha256';
    }
    public function getSignatureLength()
    {
        return 64;
    }
}
