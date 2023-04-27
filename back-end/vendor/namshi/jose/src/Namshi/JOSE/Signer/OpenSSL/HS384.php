<?php
namespace Namshi\JOSE\Signer\OpenSSL;
class HS384 extends HMAC
{
    public function getHashingAlgorithm()
    {
        return 'sha384';
    }
}
