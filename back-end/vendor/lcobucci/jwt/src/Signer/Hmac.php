<?php
namespace Lcobucci\JWT\Signer;
abstract class Hmac extends BaseSigner
{
    public function createHash($payload, Key $key)
    {
        return hash_hmac($this->getAlgorithm(), $payload, $key->getContent(), true);
    }
    public function doVerify($expected, $payload, Key $key)
    {
        if (!is_string($expected)) {
            return false;
        }
        $callback = function_exists('hash_equals') ? 'hash_equals' : [$this, 'hashEquals'];
        return call_user_func($callback, $expected, $this->createHash($payload, $key));
    }
    public function hashEquals($expected, $generated)
    {
        $expectedLength = strlen($expected);
        if ($expectedLength !== strlen($generated)) {
            return false;
        }
        $res = 0;
        for ($i = 0; $i < $expectedLength; ++$i) {
            $res |= ord($expected[$i]) ^ ord($generated[$i]);
        }
        return $res === 0;
    }
    abstract public function getAlgorithm();
}
