<?php
namespace Namshi\JOSE\Signer\OpenSSL;
class HS512 extends HMAC
{
    public function getHashingAlgorithm()
    {
        return 'sha512';
    }
}
