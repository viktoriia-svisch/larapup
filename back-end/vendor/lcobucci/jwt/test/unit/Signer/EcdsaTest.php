<?php
namespace Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Ecdsa\KeyParser;
use Mdanter\Ecc\Crypto\Signature\Signature;
use Mdanter\Ecc\Crypto\Signature\Signer;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Math\MathAdapterInterface as Adapter;
use Mdanter\Ecc\Primitives\PointInterface;
use Mdanter\Ecc\Random\RandomNumberGeneratorInterface;
class EcdsaTest extends \PHPUnit_Framework_TestCase
{
    private $adapter;
    private $signer;
    private $randomGenerator;
    private $parser;
    public function createDependencies()
    {
        $this->adapter = $this->getMock(Adapter::class);
        $this->signer = $this->getMock(Signer::class, [], [$this->adapter]);
        $this->randomGenerator = $this->getMock(RandomNumberGeneratorInterface::class);
        $this->parser = $this->getMock(KeyParser::class, [], [], '', false);
    }
    private function getSigner()
    {
        $signer = $this->getMockForAbstractClass(
            Ecdsa::class,
            [$this->adapter, $this->signer, $this->parser]
        );
        $signer->method('getSignatureLength')
               ->willReturn(64);
        $signer->method('getAlgorithm')
               ->willReturn('sha256');
        $signer->method('getAlgorithmId')
               ->willReturn('ES256');
        return $signer;
    }
    public function constructShouldConfigureDependencies()
    {
        $signer = $this->getSigner();
        $this->assertAttributeSame($this->adapter, 'adapter', $signer);
        $this->assertAttributeSame($this->signer, 'signer', $signer);
        $this->assertAttributeSame($this->parser, 'parser', $signer);
    }
    public function createHashShouldReturnAHashUsingPrivateKey()
    {
        $signer = $this->getSigner();
        $key = new Key('testing');
        $privateKey = $this->getMock(PrivateKeyInterface::class);
        $point = $this->getMock(PointInterface::class);
        $privateKey->method('getPoint')
                   ->willReturn($point);
        $point->method('getOrder')
              ->willReturn('1');
        $this->parser->expects($this->once())
                     ->method('getPrivateKey')
                     ->with($key)
                     ->willReturn($privateKey);
        $this->randomGenerator->expects($this->once())
                              ->method('generate')
                              ->with('1')
                              ->willReturn('123');
        $this->adapter->expects($this->once())
                      ->method('hexDec')
                      ->willReturn('123');
        $this->adapter->expects($this->exactly(2))
                      ->method('decHex')
                      ->willReturn('123');
        $this->signer->expects($this->once())
                     ->method('sign')
                     ->with($privateKey, $this->isType('string'), $this->isType('string'))
                     ->willReturn(new Signature('1234', '456'));
        $this->assertInternalType('string', $signer->createHash('testing', $key, $this->randomGenerator));
    }
    public function doVerifyShouldDelegateToEcdsaSignerUsingPublicKey()
    {
        $signer = $this->getSigner();
        $key = new Key('testing');
        $publicKey = $this->getMock(PublicKeyInterface::class);
        $this->parser->expects($this->once())
                     ->method('getPublicKey')
                     ->with($key)
                     ->willReturn($publicKey);
        $this->adapter->expects($this->exactly(3))
                      ->method('hexDec')
                      ->willReturn('123');
        $this->signer->expects($this->once())
                     ->method('verify')
                     ->with($publicKey, $this->isInstanceOf(Signature::class), $this->isType('string'))
                     ->willReturn(true);
        $this->assertTrue($signer->doVerify('testing', 'testing2', $key));
    }
}
