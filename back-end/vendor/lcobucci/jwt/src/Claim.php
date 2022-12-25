<?php
namespace Lcobucci\JWT;
use JsonSerializable;
interface Claim extends JsonSerializable
{
    public function getName();
    public function getValue();
    public function __toString();
}
