<?php
namespace Symfony\Component\Finder\Tests\Iterator;
class Iterator implements \Iterator
{
    protected $values = [];
    public function __construct(array $values = [])
    {
        foreach ($values as $value) {
            $this->attach(new \SplFileInfo($value));
        }
        $this->rewind();
    }
    public function attach(\SplFileInfo $fileinfo)
    {
        $this->values[] = $fileinfo;
    }
    public function rewind()
    {
        reset($this->values);
    }
    public function valid()
    {
        return false !== $this->current();
    }
    public function next()
    {
        next($this->values);
    }
    public function current()
    {
        return current($this->values);
    }
    public function key()
    {
        return key($this->values);
    }
}
