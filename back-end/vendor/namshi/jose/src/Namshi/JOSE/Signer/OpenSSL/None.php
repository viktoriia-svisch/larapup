<?php
namespace Namshi\JOSE\Signer\OpenSSL;
use Namshi\JOSE\Signer\SignerInterface;
class None implements SignerInterface
{
    public function sign($input, $key)
    {
        return '';
    }
    public function verify($key, $signature, $input)
    {
        return $signature === '';
    }
}
