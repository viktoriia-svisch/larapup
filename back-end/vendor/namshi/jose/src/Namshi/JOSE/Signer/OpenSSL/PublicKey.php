<?php
namespace Namshi\JOSE\Signer\OpenSSL;
use InvalidArgumentException;
use Namshi\JOSE\Signer\SignerInterface;
use RuntimeException;
abstract class PublicKey implements SignerInterface
{
    public function sign($input, $key, $password = null)
    {
        $keyResource = $this->getKeyResource($key, $password);
        if (!$this->supportsKey($keyResource)) {
            throw new InvalidArgumentException('Invalid key supplied.');
        }
        $signature = null;
        openssl_sign($input, $signature, $keyResource, $this->getHashingAlgorithm());
        return $signature;
    }
    public function verify($key, $signature, $input)
    {
        $keyResource = $this->getKeyResource($key);
        if (!$this->supportsKey($keyResource)) {
            throw new InvalidArgumentException('Invalid key supplied.');
        }
        $result = openssl_verify($input, $signature, $keyResource, $this->getHashingAlgorithm());
        if ($result === -1) {
            throw new RuntimeException('Unknown error during verification.');
        }
        return (bool) $result;
    }
    protected function getKeyResource($key, $password = null)
    {
        if (is_resource($key)) {
            return $key;
        }
        $resource = openssl_pkey_get_public($key) ?: openssl_pkey_get_private($key, $password);
        if ($resource === false) {
            throw new RuntimeException('Could not read key resource: ' . openssl_error_string());
        }
        return $resource;
    }
    protected function supportsKey($key)
    {
        $keyDetails = openssl_pkey_get_details($key);
        return isset($keyDetails['type']) ? $this->getSupportedPrivateKeyType() === $keyDetails['type'] : false;
    }
    abstract protected function getHashingAlgorithm();
    abstract protected function getSupportedPrivateKeyType();
}
