<?php
namespace Tymon\JWTAuth\Contracts;
interface Validator
{
    public function check($value);
    public function isValid($value);
}
