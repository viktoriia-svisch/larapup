<?php
namespace Mockery;
class Undefined
{
    public function __call($method, array $args)
    {
        return $this;
    }
    public function __toString()
    {
        return __CLASS__ . ":" . spl_object_hash($this);
    }
}
