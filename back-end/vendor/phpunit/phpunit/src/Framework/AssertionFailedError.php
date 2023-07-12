<?php
namespace PHPUnit\Framework;
class AssertionFailedError extends Exception implements SelfDescribing
{
    public function toString(): string
    {
        return $this->getMessage();
    }
}
