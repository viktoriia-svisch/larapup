<?php
namespace Lcobucci\JWT;
use InvalidArgumentException;
use Lcobucci\JWT\Claim\Factory as ClaimFactory;
use Lcobucci\JWT\Parsing\Decoder;
class Parser
{
    private $decoder;
    private $claimFactory;
    public function __construct(
        Decoder $decoder = null,
        ClaimFactory $claimFactory = null
    ) {
        $this->decoder = $decoder ?: new Decoder();
        $this->claimFactory = $claimFactory ?: new ClaimFactory();
    }
    public function parse($jwt)
    {
        $data = $this->splitJwt($jwt);
        $header = $this->parseHeader($data[0]);
        $claims = $this->parseClaims($data[1]);
        $signature = $this->parseSignature($header, $data[2]);
        foreach ($claims as $name => $value) {
            if (isset($header[$name])) {
                $header[$name] = $value;
            }
        }
        if ($signature === null) {
            unset($data[2]);
        }
        return new Token($header, $claims, $signature, $data);
    }
    protected function splitJwt($jwt)
    {
        if (!is_string($jwt)) {
            throw new InvalidArgumentException('The JWT string must have two dots');
        }
        $data = explode('.', $jwt);
        if (count($data) != 3) {
            throw new InvalidArgumentException('The JWT string must have two dots');
        }
        return $data;
    }
    protected function parseHeader($data)
    {
        $header = (array) $this->decoder->jsonDecode($this->decoder->base64UrlDecode($data));
        if (isset($header['enc'])) {
            throw new InvalidArgumentException('Encryption is not supported yet');
        }
        return $header;
    }
    protected function parseClaims($data)
    {
        $claims = (array) $this->decoder->jsonDecode($this->decoder->base64UrlDecode($data));
        foreach ($claims as $name => &$value) {
            $value = $this->claimFactory->create($name, $value);
        }
        return $claims;
    }
    protected function parseSignature(array $header, $data)
    {
        if ($data == '' || !isset($header['alg']) || $header['alg'] == 'none') {
            return null;
        }
        $hash = $this->decoder->base64UrlDecode($data);
        return new Signature($hash);
    }
}
