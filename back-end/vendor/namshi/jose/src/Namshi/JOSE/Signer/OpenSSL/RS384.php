<?php
namespace Namshi\JOSE\Signer\OpenSSL;
class RS384 extends RSA
{
    public function getHashingAlgorithm()
    {
        return version_compare(phpversion(), '5.4.8', '<') ? 'SHA384' : OPENSSL_ALGO_SHA384;
    }
}
