<?php
namespace Lcobucci\JWT\Signer\Rsa;
class Sha384Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals('RS384', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals(OPENSSL_ALGO_SHA384, $signer->getAlgorithm());
    }
}
