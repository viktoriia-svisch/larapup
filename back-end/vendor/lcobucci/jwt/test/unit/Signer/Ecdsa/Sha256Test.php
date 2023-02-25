<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
class Sha256Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha256();
        $this->assertEquals('ES256', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha256();
        $this->assertEquals('sha256', $signer->getAlgorithm());
    }
    public function getSignatureLengthMustBeCorrect()
    {
        $signer = new Sha256();
        $this->assertEquals(64, $signer->getSignatureLength());
    }
}
