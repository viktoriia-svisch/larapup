<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
use Lcobucci\JWT\Signer\Ecdsa;
class Sha512 extends Ecdsa
{
    public function getAlgorithmId()
    {
        return 'ES512';
    }
    public function getAlgorithm()
    {
        return 'sha512';
    }
    public function getSignatureLength()
    {
        return 132;
    }
}
