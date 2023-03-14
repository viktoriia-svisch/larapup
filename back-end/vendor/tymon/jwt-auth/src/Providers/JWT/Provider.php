<?php
namespace Tymon\JWTAuth\Providers\JWT;
use Illuminate\Support\Arr;
abstract class Provider
{
    protected $secret;
    protected $keys;
    protected $algo;
    public function __construct($secret, $algo, array $keys)
    {
        $this->secret = $secret;
        $this->algo = $algo;
        $this->keys = $keys;
    }
    public function setAlgo($algo)
    {
        $this->algo = $algo;
        return $this;
    }
    public function getAlgo()
    {
        return $this->algo;
    }
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }
    public function getSecret()
    {
        return $this->secret;
    }
    public function setKeys(array $keys)
    {
        $this->keys = $keys;
        return $this;
    }
    public function getKeys()
    {
        return $this->keys;
    }
    public function getPublicKey()
    {
        return Arr::get($this->keys, 'public');
    }
    public function getPrivateKey()
    {
        return Arr::get($this->keys, 'private');
    }
    public function getPassphrase()
    {
        return Arr::get($this->keys, 'passphrase');
    }
    protected function getSigningKey()
    {
        return $this->isAsymmetric() ? $this->getPrivateKey() : $this->getSecret();
    }
    protected function getVerificationKey()
    {
        return $this->isAsymmetric() ? $this->getPublicKey() : $this->getSecret();
    }
    abstract protected function isAsymmetric();
}
