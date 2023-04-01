<?php
namespace PHPUnit\Framework;
class Warning extends Exception implements SelfDescribing
{
    public function toString(): string
    {
        return $this->getMessage();
    }
}
