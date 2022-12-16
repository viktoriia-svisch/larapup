<?php
namespace Lcobucci\JWT\Signer\Hmac;
class Sha384Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals('HS384', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha384();
        $this->assertEquals('sha384', $signer->getAlgorithm());
    }
}
