<?php
namespace Namshi\JOSE;
use InvalidArgumentException;
use Namshi\JOSE\Base64\Base64Encoder;
use Namshi\JOSE\Base64\Base64UrlSafeEncoder;
use Namshi\JOSE\Base64\Encoder;
use Namshi\JOSE\Signer\SignerInterface;
class JWS extends JWT
{
    protected $signature;
    protected $isSigned = false;
    protected $originalToken;
    protected $encodedSignature;
    protected $encryptionEngine;
    protected $supportedEncryptionEngines = array('OpenSSL', 'SecLib');
    public function __construct($header = array(), $encryptionEngine = 'OpenSSL')
    {
        if (!in_array($encryptionEngine, $this->supportedEncryptionEngines)) {
            throw new InvalidArgumentException(sprintf('Encryption engine %s is not supported', $encryptionEngine));
        }
        if ('SecLib' === $encryptionEngine && version_compare(PHP_VERSION, '7.0.0-dev') >= 0) {
            throw new InvalidArgumentException("phpseclib 1.0.0(LTS), even the latest 2.0.0, doesn't support PHP7 yet");
        }
        $this->encryptionEngine = $encryptionEngine;
        parent::__construct(array(), $header);
    }
    public function sign($key, $password = null)
    {
        $this->signature = $this->getSigner()->sign($this->generateSigninInput(), $key, $password);
        $this->isSigned = true;
        return $this->signature;
    }
    public function getSignature()
    {
        if ($this->isSigned()) {
            return $this->signature;
        }
        return;
    }
    public function isSigned()
    {
        return (bool) $this->isSigned;
    }
    public function getTokenString()
    {
        $signinInput = $this->generateSigninInput();
        return sprintf('%s.%s', $signinInput, $this->encoder->encode($this->getSignature()));
    }
    public static function load($jwsTokenString, $allowUnsecure = false, Encoder $encoder = null, $encryptionEngine = 'OpenSSL')
    {
        if ($encoder === null) {
            $encoder = strpbrk($jwsTokenString, '+/=') ? new Base64Encoder() : new Base64UrlSafeEncoder();
        }
        $parts = explode('.', $jwsTokenString);
        if (count($parts) === 3) {
            $header = json_decode($encoder->decode($parts[0]), true);
            $payload = json_decode($encoder->decode($parts[1]), true);
            if (is_array($header) && is_array($payload)) {
                if (strtolower($header['alg']) === 'none' && !$allowUnsecure) {
                    throw new InvalidArgumentException(sprintf('The token "%s" cannot be validated in a secure context, as it uses the unallowed "none" algorithm', $jwsTokenString));
                }
                $jws = new static($header, $encryptionEngine);
                $jws->setEncoder($encoder)
                    ->setHeader($header)
                    ->setPayload($payload)
                    ->setOriginalToken($jwsTokenString)
                    ->setEncodedSignature($parts[2]);
                return $jws;
            }
        }
        throw new InvalidArgumentException(sprintf('The token "%s" is an invalid JWS', $jwsTokenString));
    }
    public function verify($key, $algo = null)
    {
        if (empty($key) || ($algo && $this->header['alg'] !== $algo)) {
            return false;
        }
        $decodedSignature = $this->encoder->decode($this->getEncodedSignature());
        $signinInput = $this->getSigninInput();
        return $this->getSigner()->verify($key, $decodedSignature, $signinInput);
    }
    private function getSigninInput()
    {
        $parts = explode('.', $this->originalToken);
        if (count($parts) >= 2) {
            return sprintf('%s.%s', $parts[0], $parts[1]);
        }
        return $this->generateSigninInput();
    }
    private function setOriginalToken($originalToken)
    {
        $this->originalToken = $originalToken;
        return $this;
    }
    public function getEncodedSignature()
    {
        return $this->encodedSignature;
    }
    public function setEncodedSignature($encodedSignature)
    {
        $this->encodedSignature = $encodedSignature;
        return $this;
    }
    protected function getSigner()
    {
        $signerClass = sprintf('Namshi\\JOSE\\Signer\\%s\\%s', $this->encryptionEngine, $this->header['alg']);
        if (class_exists($signerClass)) {
            return new $signerClass();
        }
        throw new InvalidArgumentException(
            sprintf("The algorithm '%s' is not supported for %s", $this->header['alg'], $this->encryptionEngine));
    }
}
