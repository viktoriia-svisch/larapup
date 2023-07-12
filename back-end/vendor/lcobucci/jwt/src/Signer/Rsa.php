<?php
namespace Lcobucci\JWT\Signer;
use InvalidArgumentException;
abstract class Rsa extends BaseSigner
{
    public function createHash($payload, Key $key)
    {
        $key = openssl_get_privatekey($key->getContent(), $key->getPassphrase());
        $this->validateKey($key);
        $signature = '';
        if (!openssl_sign($payload, $signature, $key, $this->getAlgorithm())) {
            throw new InvalidArgumentException(
                'There was an error while creating the signature: ' . openssl_error_string()
            );
        }
        return $signature;
    }
    public function doVerify($expected, $payload, Key $key)
    {
        $key = openssl_get_publickey($key->getContent());
        $this->validateKey($key);
        return openssl_verify($payload, $expected, $key, $this->getAlgorithm()) === 1;
    }
    private function validateKey($key)
    {
        if ($key === false) {
            throw new InvalidArgumentException(
                'It was not possible to parse your key, reason: ' . openssl_error_string()
            );
        }
        $details = openssl_pkey_get_details($key);
        if (!isset($details['key']) || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new InvalidArgumentException('This key is not compatible with RSA signatures');
        }
    }
    abstract public function getAlgorithm();
}
