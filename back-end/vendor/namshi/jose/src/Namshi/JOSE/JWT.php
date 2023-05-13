<?php
namespace Namshi\JOSE;
use Namshi\JOSE\Base64\Base64UrlSafeEncoder;
use Namshi\JOSE\Base64\Encoder;
class JWT
{
    protected $payload;
    protected $header;
    protected $encoder;
    public function __construct(array $payload, array $header)
    {
        $this->setPayload($payload);
        $this->setHeader($header);
        $this->setEncoder(new Base64UrlSafeEncoder());
    }
    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }
    public function generateSigninInput()
    {
        $base64payload = $this->encoder->encode(json_encode($this->getPayload(), JSON_UNESCAPED_SLASHES));
        $base64header = $this->encoder->encode(json_encode($this->getHeader(), JSON_UNESCAPED_SLASHES));
        return sprintf('%s.%s', $base64header, $base64payload);
    }
    public function getPayload()
    {
        return $this->payload;
    }
    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }
    public function getHeader()
    {
        return $this->header;
    }
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }
}
