<?php
namespace Namshi\JOSE;
class SimpleJWS extends JWS
{
    public function __construct($header = array(), $encryptionEngine = 'OpenSSL')
    {
        if (!isset($header['typ'])) {
            $header['typ'] = 'JWS';
        }
        parent::__construct($header, $encryptionEngine);
    }
    public function setPayload(array $payload)
    {
        if (!isset($payload['iat'])) {
            $payload['iat'] = time();
        }
        return parent::setPayload($payload);
    }
    public function isValid($key, $algo = null)
    {
        return $this->verify($key, $algo) && !$this->isExpired();
    }
    public function isExpired()
    {
        $payload = $this->getPayload();
        if (isset($payload['exp'])) {
            $now = new \DateTime('now');
            if (is_int($payload['exp'])) {
                return ($now->getTimestamp() - $payload['exp']) > 0;
            }
            if (is_numeric($payload['exp'])) {
                return ($now->format('U') - $payload['exp']) > 0;
            }
        }
        return false;
    }
}
