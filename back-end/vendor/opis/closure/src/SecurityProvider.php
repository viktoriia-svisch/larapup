<?php
namespace Opis\Closure;
class SecurityProvider implements ISecurityProvider
{
    protected $secret;
    public function __construct($secret)
    {
        $this->secret = $secret;
    }
    public function sign($closure)
    {
        return array(
            'closure' => $closure,
            'hash' => base64_encode(hash_hmac('sha256', $closure, $this->secret, true)),
        );
    }
    public function verify(array $data)
    {
        return base64_encode(hash_hmac('sha256', $data['closure'], $this->secret, true)) === $data['hash'];
    }
}
