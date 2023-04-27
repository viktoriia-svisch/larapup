<?php
namespace Tymon\JWTAuth;
use Tymon\JWTAuth\Validators\TokenValidator;
class Token
{
    private $value;
    public function __construct($value)
    {
        $this->value = (string) (new TokenValidator)->check($value);
    }
    public function get()
    {
        return $this->value;
    }
    public function __toString()
    {
        return $this->get();
    }
}
