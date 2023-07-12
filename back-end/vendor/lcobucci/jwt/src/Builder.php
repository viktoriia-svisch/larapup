<?php
namespace Lcobucci\JWT;
use BadMethodCallException;
use Lcobucci\JWT\Claim\Factory as ClaimFactory;
use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Signer\Key;
class Builder
{
    private $headers;
    private $claims;
    private $signature;
    private $encoder;
    private $claimFactory;
    public function __construct(
        Encoder $encoder = null,
        ClaimFactory $claimFactory = null
    ) {
        $this->encoder = $encoder ?: new Encoder();
        $this->claimFactory = $claimFactory ?: new ClaimFactory();
        $this->headers = ['typ'=> 'JWT', 'alg' => 'none'];
        $this->claims = [];
    }
    public function setAudience($audience, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('aud', (string) $audience, $replicateAsHeader);
    }
    public function setExpiration($expiration, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('exp', (int) $expiration, $replicateAsHeader);
    }
    public function setId($id, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('jti', (string) $id, $replicateAsHeader);
    }
    public function setIssuedAt($issuedAt, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('iat', (int) $issuedAt, $replicateAsHeader);
    }
    public function setIssuer($issuer, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('iss', (string) $issuer, $replicateAsHeader);
    }
    public function setNotBefore($notBefore, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('nbf', (int) $notBefore, $replicateAsHeader);
    }
    public function setSubject($subject, $replicateAsHeader = false)
    {
        return $this->setRegisteredClaim('sub', (string) $subject, $replicateAsHeader);
    }
    protected function setRegisteredClaim($name, $value, $replicate)
    {
        $this->set($name, $value);
        if ($replicate) {
            $this->headers[$name] = $this->claims[$name];
        }
        return $this;
    }
    public function setHeader($name, $value)
    {
        if ($this->signature) {
            throw new BadMethodCallException('You must unsign before make changes');
        }
        $this->headers[(string) $name] = $this->claimFactory->create($name, $value);
        return $this;
    }
    public function set($name, $value)
    {
        if ($this->signature) {
            throw new BadMethodCallException('You must unsign before making changes');
        }
        $this->claims[(string) $name] = $this->claimFactory->create($name, $value);
        return $this;
    }
    public function sign(Signer $signer, $key)
    {
        $signer->modifyHeader($this->headers);
        $this->signature = $signer->sign(
            $this->getToken()->getPayload(),
            $key
        );
        return $this;
    }
    public function unsign()
    {
        $this->signature = null;
        return $this;
    }
    public function getToken()
    {
        $payload = [
            $this->encoder->base64UrlEncode($this->encoder->jsonEncode($this->headers)),
            $this->encoder->base64UrlEncode($this->encoder->jsonEncode($this->claims))
        ];
        if ($this->signature !== null) {
            $payload[] = $this->encoder->base64UrlEncode($this->signature);
        }
        return new Token($this->headers, $this->claims, $this->signature, $payload);
    }
}
