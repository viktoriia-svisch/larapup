<?php
namespace Lcobucci\JWT;
class ValidationData
{
    private $items;
    public function __construct($currentTime = null)
    {
        $currentTime = $currentTime ?: time();
        $this->items = [
            'jti' => null,
            'iss' => null,
            'aud' => null,
            'sub' => null,
            'iat' => $currentTime,
            'nbf' => $currentTime,
            'exp' => $currentTime
        ];
    }
    public function setId($id)
    {
        $this->items['jti'] = (string) $id;
    }
    public function setIssuer($issuer)
    {
        $this->items['iss'] = (string) $issuer;
    }
    public function setAudience($audience)
    {
        $this->items['aud'] = (string) $audience;
    }
    public function setSubject($subject)
    {
        $this->items['sub'] = (string) $subject;
    }
    public function setCurrentTime($currentTime)
    {
        $this->items['iat'] = (int) $currentTime;
        $this->items['nbf'] = (int) $currentTime;
        $this->items['exp'] = (int) $currentTime;
    }
    public function get($name)
    {
        return isset($this->items[$name]) ? $this->items[$name] : null;
    }
    public function has($name)
    {
        return !empty($this->items[$name]);
    }
}
