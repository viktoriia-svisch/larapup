<?php
namespace Lcobucci\JWT\Signer\Rsa;
class Sha512Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals('RS512', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals(OPENSSL_ALGO_SHA512, $signer->getAlgorithm());
    }
}
