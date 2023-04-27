<?php
namespace Lcobucci\JWT\Signer\Hmac;
class Sha512Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals('HS512', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha512();
        $this->assertEquals('sha512', $signer->getAlgorithm());
    }
}
