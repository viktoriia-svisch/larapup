<?php
namespace Symfony\Component\Console\Helper;
class TableRows implements \IteratorAggregate
{
    private $generator;
    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }
    public function getIterator()
    {
        $g = $this->generator;
        return $g();
    }
}
