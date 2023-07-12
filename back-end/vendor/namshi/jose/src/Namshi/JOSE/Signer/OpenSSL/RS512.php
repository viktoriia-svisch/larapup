<?php
namespace Namshi\JOSE\Signer\OpenSSL;
class RS512 extends RSA
{
    public function getHashingAlgorithm()
    {
        return version_compare(phpversion(), '5.4.8', '<') ? 'SHA512' : OPENSSL_ALGO_SHA512;
    }
}
