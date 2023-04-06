<?php
namespace Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Ecdsa\KeyParser;
use Mdanter\Ecc\Crypto\Signature\Signature;
use Mdanter\Ecc\Crypto\Signature\Signer;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Math\MathAdapterInterface as Adapter;
use Mdanter\Ecc\Random\RandomGeneratorFactory;
use Mdanter\Ecc\Random\RandomNumberGeneratorInterface;
abstract class Ecdsa extends BaseSigner
{
    private $adapter;
    private $signer;
    private $parser;
    public function __construct(Adapter $adapter = null, Signer $signer = null, KeyParser $parser = null)
    {
        $this->adapter = $adapter ?: EccFactory::getAdapter();
        $this->signer = $signer ?: EccFactory::getSigner($this->adapter);
        $this->parser = $parser ?: new KeyParser($this->adapter);
    }
    public function createHash(
        $payload,
        Key $key,
        RandomNumberGeneratorInterface $generator = null
    ) {
        $privateKey = $this->parser->getPrivateKey($key);
        $generator = $generator ?: RandomGeneratorFactory::getRandomGenerator();
        return $this->createSignatureHash(
            $this->signer->sign(
                $privateKey,
                $this->createSigningHash($payload),
                $generator->generate($privateKey->getPoint()->getOrder())
            )
        );
    }
    private function createSignatureHash(Signature $signature)
    {
        $length = $this->getSignatureLength();
        return pack(
            'H*',
            sprintf(
                '%s%s',
                str_pad($this->adapter->decHex($signature->getR()), $length, '0', STR_PAD_LEFT),
                str_pad($this->adapter->decHex($signature->getS()), $length, '0', STR_PAD_LEFT)
            )
        );
    }
    private function createSigningHash($payload)
    {
        return $this->adapter->hexDec(hash($this->getAlgorithm(), $payload));
    }
    public function doVerify($expected, $payload, Key $key)
    {
        return $this->signer->verify(
            $this->parser->getPublicKey($key),
            $this->extractSignature($expected),
            $this->createSigningHash($payload)
        );
    }
    private function extractSignature($value)
    {
        $length = $this->getSignatureLength();
        $value = unpack('H*', $value)[1];
        return new Signature(
            $this->adapter->hexDec(substr($value, 0, $length)),
            $this->adapter->hexDec(substr($value, $length))
        );
    }
    abstract public function getSignatureLength();
    abstract public function getAlgorithm();
}
