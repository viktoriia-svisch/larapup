<?php
namespace Namshi\JOSE\Signer\OpenSSL;
class ES512 extends ECDSA
{
    public function getHashingAlgorithm()
    {
        return version_compare(phpversion(), '5.4.8', '<') ? 'SHA512' : OPENSSL_ALGO_SHA512;
    }
    protected function getSupportedECDSACurve()
    {
        return '1.3.132.0.35';
    }
}
