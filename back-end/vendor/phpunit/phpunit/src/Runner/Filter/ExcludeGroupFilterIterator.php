<?php
namespace PHPUnit\Runner\Filter;
class ExcludeGroupFilterIterator extends GroupFilterIterator
{
    protected function doAccept(string $hash): bool
    {
        return !\in_array($hash, $this->groupTests, true);
    }
}
