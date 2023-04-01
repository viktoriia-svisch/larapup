<?php
namespace Lcobucci\JWT\Signer\Hmac;
class Sha256Test extends \PHPUnit_Framework_TestCase
{
    public function getAlgorithmIdMustBeCorrect()
    {
        $signer = new Sha256();
        $this->assertEquals('HS256', $signer->getAlgorithmId());
    }
    public function getAlgorithmMustBeCorrect()
    {
        $signer = new Sha256();
        $this->assertEquals('sha256', $signer->getAlgorithm());
    }
}
