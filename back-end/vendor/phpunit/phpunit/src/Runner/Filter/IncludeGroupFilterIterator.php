<?php
namespace PHPUnit\Runner\Filter;
class IncludeGroupFilterIterator extends GroupFilterIterator
{
    protected function doAccept(string $hash): bool
    {
        return \in_array($hash, $this->groupTests, true);
    }
}
