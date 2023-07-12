<?php
namespace Illuminate\Contracts\Hashing;
interface Hasher
{
    public function info($hashedValue);
    public function make($value, array $options = []);
    public function check($value, $hashedValue, array $options = []);
    public function needsRehash($hashedValue, array $options = []);
}
