<?php
namespace Namshi\JOSE\Signer\OpenSSL;
use Namshi\JOSE\Signer\SignerInterface;
abstract class HMAC implements SignerInterface
{
    public function sign($input, $key)
    {
        return hash_hmac($this->getHashingAlgorithm(), $input, (string) $key, true);
    }
    public function verify($key, $signature, $input)
    {
        $signedInput = $this->sign($input, $key);
        return $this->timingSafeEquals($signedInput, $signature);
    }
    public function timingSafeEquals($known, $input)
    {
        return hash_equals($known, $input);
    }
    abstract public function getHashingAlgorithm();
}
