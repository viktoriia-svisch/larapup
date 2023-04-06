<?php
namespace Lcobucci\JWT;
use BadMethodCallException;
use DateTime;
use DateTimeInterface;
use Generator;
use Lcobucci\JWT\Claim\Validatable;
use OutOfBoundsException;
class Token
{
    private $headers;
    private $claims;
    private $signature;
    private $payload;
    public function __construct(
        array $headers = ['alg' => 'none'],
        array $claims = [],
        Signature $signature = null,
        array $payload = ['', '']
    ) {
        $this->headers = $headers;
        $this->claims = $claims;
        $this->signature = $signature;
        $this->payload = $payload;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
    public function hasHeader($name)
    {
        return array_key_exists($name, $this->headers);
    }
    public function getHeader($name, $default = null)
    {
        if ($this->hasHeader($name)) {
            return $this->getHeaderValue($name);
        }
        if ($default === null) {
            throw new OutOfBoundsException('Requested header is not configured');
        }
        return $default;
    }
    private function getHeaderValue($name)
    {
        $header = $this->headers[$name];
        if ($header instanceof Claim) {
            return $header->getValue();
        }
        return $header;
    }
    public function getClaims()
    {
        return $this->claims;
    }
    public function hasClaim($name)
    {
        return array_key_exists($name, $this->claims);
    }
    public function getClaim($name, $default = null)
    {
        if ($this->hasClaim($name)) {
            return $this->claims[$name]->getValue();
        }
        if ($default === null) {
            throw new OutOfBoundsException('Requested claim is not configured');
        }
        return $default;
    }
    public function verify(Signer $signer, $key)
    {
        if ($this->signature === null) {
            throw new BadMethodCallException('This token is not signed');
        }
        if ($this->headers['alg'] !== $signer->getAlgorithmId()) {
            return false;
        }
        return $this->signature->verify($signer, $this->getPayload(), $key);
    }
    public function validate(ValidationData $data)
    {
        foreach ($this->getValidatableClaims() as $claim) {
            if (!$claim->validate($data)) {
                return false;
            }
        }
        return true;
    }
    public function isExpired(DateTimeInterface $now = null)
    {
        $exp = $this->getClaim('exp', false);
        if ($exp === false) {
            return false;
        }
        $now = $now ?: new DateTime();
        $expiresAt = new DateTime();
        $expiresAt->setTimestamp($exp);
        return $now > $expiresAt;
    }
    private function getValidatableClaims()
    {
        foreach ($this->claims as $claim) {
            if ($claim instanceof Validatable) {
                yield $claim;
            }
        }
    }
    public function getPayload()
    {
        return $this->payload[0] . '.' . $this->payload[1];
    }
    public function __toString()
    {
        $data = implode('.', $this->payload);
        if ($this->signature === null) {
            $data .= '.';
        }
        return $data;
    }
}
