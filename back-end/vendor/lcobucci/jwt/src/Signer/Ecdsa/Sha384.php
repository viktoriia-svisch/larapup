<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
use Lcobucci\JWT\Signer\Ecdsa;
class Sha384 extends Ecdsa
{
    public function getAlgorithmId()
    {
        return 'ES384';
    }
    public function getAlgorithm()
    {
        return 'sha384';
    }
    public function getSignatureLength()
    {
        return 96;
    }
}
