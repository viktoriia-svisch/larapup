<?php
namespace Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer;
abstract class BaseSigner implements Signer
{
    public function modifyHeader(array &$headers)
    {
        $headers['alg'] = $this->getAlgorithmId();
    }
    public function sign($payload, $key)
    {
        return new Signature($this->createHash($payload, $this->getKey($key)));
    }
    public function verify($expected, $payload, $key)
    {
        return $this->doVerify($expected, $payload, $this->getKey($key));
    }
    private function getKey($key)
    {
        if (is_string($key)) {
            $key = new Key($key);
        }
        return $key;
    }
    abstract public function createHash($payload, Key $key);
    abstract public function doVerify($expected, $payload, Key $key);
}
