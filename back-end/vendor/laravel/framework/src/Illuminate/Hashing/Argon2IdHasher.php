<?php
namespace Illuminate\Hashing;
use RuntimeException;
class Argon2IdHasher extends ArgonHasher
{
    public function check($value, $hashedValue, array $options = [])
    {
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'argon2id') {
            throw new RuntimeException('This password does not use the Argon2id algorithm.');
        }
        if (strlen($hashedValue) === 0) {
            return false;
        }
        return password_verify($value, $hashedValue);
    }
    protected function algorithm()
    {
        return PASSWORD_ARGON2ID;
    }
}
