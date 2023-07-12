<?php
namespace Lcobucci\JWT\Signer\Ecdsa;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Math\MathAdapterInterface;
use Mdanter\Ecc\Serializer\PrivateKey\PrivateKeySerializerInterface;
use Mdanter\Ecc\Serializer\PublicKey\PublicKeySerializerInterface;
use Lcobucci\JWT\Signer\Key;
class KeyParserTest extends \PHPUnit_Framework_TestCase
{
    private $adapter;
    private $privateKeySerializer;
    private $publicKeySerializer;
    public function createDependencies()
    {
        $this->adapter = $this->getMock(MathAdapterInterface::class);
        $this->privateKeySerializer = $this->getMock(PrivateKeySerializerInterface::class);
        $this->publicKeySerializer = $this->getMock(PublicKeySerializerInterface::class);
    }
    public function constructShouldConfigureDependencies()
    {
        $parser = new KeyParser($this->adapter, $this->privateKeySerializer, $this->publicKeySerializer);
        $this->assertAttributeSame($this->privateKeySerializer, 'privateKeySerializer', $parser);
        $this->assertAttributeSame($this->publicKeySerializer, 'publicKeySerializer', $parser);
    }
    public function getPrivateKeyShouldAskSerializerToParseTheKey()
    {
        $privateKey = $this->getMock(PrivateKeyInterface::class);
        $keyContent = 'MHcCAQEEIBGpMoZJ64MMSzuo5JbmXpf9V4qSWdLIl/8RmJLcfn/qoAoGC'
                      . 'CqGSM49AwEHoUQDQgAE7it/EKmcv9bfpcV1fBreLMRXxWpnd0wxa2iF'
                      . 'ruiI2tsEdGFTLTsyU+GeRqC7zN0aTnTQajarUylKJ3UWr/r1kg==';
        $this->privateKeySerializer->expects($this->once())
                                   ->method('parse')
                                   ->with($keyContent)
                                   ->willReturn($privateKey);
        $parser = new KeyParser($this->adapter, $this->privateKeySerializer, $this->publicKeySerializer);
        $this->assertSame($privateKey, $parser->getPrivateKey($this->getPrivateKey()));
    }
    public function getPrivateKeyShouldRaiseExceptionWhenAWrongKeyWasGiven()
    {
        $this->privateKeySerializer->expects($this->never())
                                   ->method('parse');
        $parser = new KeyParser($this->adapter, $this->privateKeySerializer, $this->publicKeySerializer);
        $parser->getPrivateKey($this->getPublicKey());
    }
    public function getPublicKeyShouldAskSerializerToParseTheKey()
    {
        $publicKey = $this->getMock(PublicKeyInterface::class);
        $keyContent = 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE7it/EKmcv9bfpcV1fBreLMRXxWpn'
                      . 'd0wxa2iFruiI2tsEdGFTLTsyU+GeRqC7zN0aTnTQajarUylKJ3UWr/r1kg==';
        $this->publicKeySerializer->expects($this->once())
                                  ->method('parse')
                                  ->with($keyContent)
                                  ->willReturn($publicKey);
        $parser = new KeyParser($this->adapter, $this->privateKeySerializer, $this->publicKeySerializer);
        $this->assertSame($publicKey, $parser->getPublicKey($this->getPublicKey()));
    }
    public function getPublicKeyShouldRaiseExceptionWhenAWrongKeyWasGiven()
    {
        $this->publicKeySerializer->expects($this->never())
                                  ->method('parse');
        $parser = new KeyParser($this->adapter, $this->privateKeySerializer, $this->publicKeySerializer);
        $parser->getPublicKey($this->getPrivateKey());
    }
    private function getPrivateKey()
    {
        return new Key(
            "-----BEGIN EC PRIVATE KEY-----\n"
            . "MHcCAQEEIBGpMoZJ64MMSzuo5JbmXpf9V4qSWdLIl/8RmJLcfn/qoAoGCCqGSM49\n"
            . "AwEHoUQDQgAE7it/EKmcv9bfpcV1fBreLMRXxWpnd0wxa2iFruiI2tsEdGFTLTsy\n"
            . "U+GeRqC7zN0aTnTQajarUylKJ3UWr/r1kg==\n"
            . "-----END EC PRIVATE KEY-----"
        );
    }
    private function getPublicKey()
    {
        return new Key(
            "-----BEGIN PUBLIC KEY-----\n"
            . "MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE7it/EKmcv9bfpcV1fBreLMRXxWpn\n"
            . "d0wxa2iFruiI2tsEdGFTLTsyU+GeRqC7zN0aTnTQajarUylKJ3UWr/r1kg==\n"
            . "-----END PUBLIC KEY-----"
        );
    }
}
