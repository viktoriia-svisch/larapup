<?php
namespace Illuminate\Hashing;
use Illuminate\Support\Manager;
use Illuminate\Contracts\Hashing\Hasher;
class HashManager extends Manager implements Hasher
{
    public function createBcryptDriver()
    {
        return new BcryptHasher($this->app['config']['hashing.bcrypt'] ?? []);
    }
    public function createArgonDriver()
    {
        return new ArgonHasher($this->app['config']['hashing.argon'] ?? []);
    }
    public function createArgon2idDriver()
    {
        return new Argon2IdHasher($this->app['config']['hashing.argon'] ?? []);
    }
    public function info($hashedValue)
    {
        return $this->driver()->info($hashedValue);
    }
    public function make($value, array $options = [])
    {
        return $this->driver()->make($value, $options);
    }
    public function check($value, $hashedValue, array $options = [])
    {
        return $this->driver()->check($value, $hashedValue, $options);
    }
    public function needsRehash($hashedValue, array $options = [])
    {
        return $this->driver()->needsRehash($hashedValue, $options);
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['hashing.driver'] ?? 'bcrypt';
    }
}
