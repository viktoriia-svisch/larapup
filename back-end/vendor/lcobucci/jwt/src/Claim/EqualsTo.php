<?php
namespace Lcobucci\JWT\Claim;
use Lcobucci\JWT\Claim;
use Lcobucci\JWT\ValidationData;
class EqualsTo extends Basic implements Claim, Validatable
{
    public function validate(ValidationData $data)
    {
        if ($data->has($this->getName())) {
            return $this->getValue() === $data->get($this->getName());
        }
        return true;
    }
}
