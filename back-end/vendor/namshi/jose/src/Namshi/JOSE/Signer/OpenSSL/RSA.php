<?php
namespace Namshi\JOSE\Signer\OpenSSL;
abstract class RSA extends PublicKey
{
    protected function getSupportedPrivateKeyType()
    {
        return defined('OPENSSL_KEYTYPE_RSA') ? OPENSSL_KEYTYPE_RSA : false;
    }
}
