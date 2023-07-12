<?php
namespace Tymon\JWTAuth\Claims;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Tymon\JWTAuth\Contracts\Claim as ClaimContract;
abstract class Claim implements Arrayable, ClaimContract, Jsonable, JsonSerializable
{
    protected $name;
    private $value;
    public function __construct($value)
    {
        $this->setValue($value);
    }
    public function setValue($value)
    {
        $this->value = $this->validateCreate($value);
        return $this;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    public function getName()
    {
        return $this->name;
    }
    public function validateCreate($value)
    {
        return $value;
    }
    public function validatePayload()
    {
        return $this->getValue();
    }
    public function validateRefresh($refreshTTL)
    {
        return $this->getValue();
    }
    public function matches($value, $strict = true)
    {
        return $strict ? $this->value === $value : $this->value == $value;
    }
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    public function toArray()
    {
        return [$this->getName() => $this->getValue()];
    }
    public function toJson($options = JSON_UNESCAPED_SLASHES)
    {
        return json_encode($this->toArray(), $options);
    }
    public function __toString()
    {
        return $this->toJson();
    }
}
