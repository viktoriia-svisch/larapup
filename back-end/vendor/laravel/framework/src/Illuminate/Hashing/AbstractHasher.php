<?php
namespace Illuminate\Hashing;
abstract class AbstractHasher
{
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }
    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }
        return password_verify($value, $hashedValue);
    }
}
