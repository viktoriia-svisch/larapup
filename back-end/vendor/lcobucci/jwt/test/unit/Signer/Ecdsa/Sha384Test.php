<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
class Sha384Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals('ES384', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals('sha384', $signer->getAlgorithm());
    }
    public function getSignatureLengthMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals(96, $signer->getSignatureLength());
    }
}
