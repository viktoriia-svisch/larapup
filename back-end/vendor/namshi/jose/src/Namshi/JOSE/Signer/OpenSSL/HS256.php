<?php
namespace Namshi\JOSE\Signer\OpenSSL;
class HS256 extends HMAC
{
    public function getHashingAlgorithm()
    {
        return 'sha256';
    }
}
