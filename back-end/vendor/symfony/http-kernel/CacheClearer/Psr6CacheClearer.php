<?php
namespace Symfony\Component\HttpKernel\CacheClearer;
class Psr6CacheClearer implements CacheClearerInterface
{
    private $pools = [];
    public function __construct(array $pools = [])
    {
        $this->pools = $pools;
    }
    public function hasPool($name)
    {
        return isset($this->pools[$name]);
    }
    public function getPool($name)
    {
        if (!$this->hasPool($name)) {
            throw new \InvalidArgumentException(sprintf('Cache pool not found: %s.', $name));
        }
        return $this->pools[$name];
    }
    public function clearPool($name)
    {
        if (!isset($this->pools[$name])) {
            throw new \InvalidArgumentException(sprintf('Cache pool not found: %s.', $name));
        }
        return $this->pools[$name]->clear();
    }
    public function clear($cacheDir)
    {
        foreach ($this->pools as $pool) {
            $pool->clear();
        }
    }
}
