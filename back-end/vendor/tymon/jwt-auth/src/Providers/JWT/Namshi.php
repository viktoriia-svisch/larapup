<?php
namespace Tymon\JWTAuth\Providers\JWT;
use Exception;
use Namshi\JOSE\JWS;
use ReflectionClass;
use ReflectionException;
use InvalidArgumentException;
use Namshi\JOSE\Signer\OpenSSL\PublicKey;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
class Namshi extends Provider implements JWT
{
    protected $jws;
    public function __construct(JWS $jws, $secret, $algo, array $keys)
    {
        parent::__construct($secret, $algo, $keys);
        $this->jws = $jws;
    }
    public function encode(array $payload)
    {
        try {
            $this->jws->setPayload($payload)->sign($this->getSigningKey(), $this->getPassphrase());
            return (string) $this->jws->getTokenString();
        } catch (Exception $e) {
            throw new JWTException('Could not create token: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    public function decode($token)
    {
        try {
            $jws = $this->jws->load($token, false);
        } catch (InvalidArgumentException $e) {
            throw new TokenInvalidException('Could not decode token: '.$e->getMessage(), $e->getCode(), $e);
        }
        if (! $jws->verify($this->getVerificationKey(), $this->getAlgo())) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }
        return (array) $jws->getPayload();
    }
    protected function isAsymmetric()
    {
        try {
            return (new ReflectionClass(sprintf('Namshi\\JOSE\\Signer\\OpenSSL\\%s', $this->getAlgo())))->isSubclassOf(PublicKey::class);
        } catch (ReflectionException $e) {
            throw new JWTException('The given algorithm could not be found', $e->getCode(), $e);
        }
    }
}
