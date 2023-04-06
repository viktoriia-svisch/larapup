<?php
namespace Lcobucci\JWT;
use InvalidArgumentException;
use Lcobucci\JWT\Signer\Key;
interface Signer
{
    public function getAlgorithmId();
    public function modifyHeader(array &$headers);
    public function sign($payload, $key);
    public function verify($expected, $payload, $key);
}
