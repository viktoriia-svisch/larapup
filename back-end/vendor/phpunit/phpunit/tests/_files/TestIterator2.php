<?php
class TestIterator2 implements Iterator
{
    protected $data;
    public function __construct(array $array)
    {
        $this->data = $array;
    }
    public function current()
    {
        return \current($this->data);
    }
    public function next(): void
    {
        \next($this->data);
    }
    public function key()
    {
        return \key($this->data);
    }
    public function valid()
    {
        return \key($this->data) !== null;
    }
    public function rewind(): void
    {
        \reset($this->data);
    }
}
