<?php
class SampleArrayAccess implements ArrayAccess
{
    private $container;
    public function __construct()
    {
        $this->container = [];
    }
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }
}
