<?php
namespace Namshi\JOSE\Signer;
interface SignerInterface
{
    public function sign($input, $key);
    public function verify($key, $signature, $input);
}
