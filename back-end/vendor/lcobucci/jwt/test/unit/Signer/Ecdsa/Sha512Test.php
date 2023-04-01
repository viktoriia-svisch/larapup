<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
class Sha512Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals('ES512', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals('sha512', $signer->getAlgorithm());
    }
    public function getSignatureLengthMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals(132, $signer->getSignatureLength());
    }
}
