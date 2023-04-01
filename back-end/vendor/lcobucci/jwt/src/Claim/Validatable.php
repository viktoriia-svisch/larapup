<?php
namespace Lcobucci\JWT\Claim;
use Lcobucci\JWT\ValidationData;
interface Validatable
{
    public function validate(ValidationData $data);
}
