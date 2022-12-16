<?php
namespace Illuminate\Hashing;
use RuntimeException;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
class ArgonHasher extends AbstractHasher implements HasherContract
{
    protected $memory = 1024;
    protected $time = 2;
    protected $threads = 2;
    protected $verifyAlgorithm = false;
    public function __construct(array $options = [])
    {
        $this->time = $options['time'] ?? $this->time;
        $this->memory = $options['memory'] ?? $this->memory;
        $this->threads = $options['threads'] ?? $this->threads;
        $this->verifyAlgorithm = $options['verify'] ?? $this->verifyAlgorithm;
    }
    public function make($value, array $options = [])
    {
        $hash = password_hash($value, $this->algorithm(), [
            'memory_cost' => $this->memory($options),
            'time_cost' => $this->time($options),
            'threads' => $this->threads($options),
        ]);
        if ($hash === false) {
            throw new RuntimeException('Argon2 hashing not supported.');
        }
        return $hash;
    }
    protected function algorithm()
    {
        return PASSWORD_ARGON2I;
    }
    public function check($value, $hashedValue, array $options = [])
    {
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'argon2i') {
            throw new RuntimeException('This password does not use the Argon2i algorithm.');
        }
        return parent::check($value, $hashedValue, $options);
    }
    public function needsRehash($hashedValue, array $options = [])
    {
        return password_needs_rehash($hashedValue, $this->algorithm(), [
            'memory_cost' => $this->memory($options),
            'time_cost' => $this->time($options),
            'threads' => $this->threads($options),
        ]);
    }
    public function setMemory(int $memory)
    {
        $this->memory = $memory;
        return $this;
    }
    public function setTime(int $time)
    {
        $this->time = $time;
        return $this;
    }
    public function setThreads(int $threads)
    {
        $this->threads = $threads;
        return $this;
    }
    protected function memory(array $options)
    {
        return $options['memory'] ?? $this->memory;
    }
    protected function time(array $options)
    {
        return $options['time'] ?? $this->time;
    }
    protected function threads(array $options)
    {
        return $options['threads'] ?? $this->threads;
    }
}
