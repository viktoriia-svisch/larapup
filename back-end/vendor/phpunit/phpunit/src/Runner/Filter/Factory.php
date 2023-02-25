<?php
namespace PHPUnit\Runner\Filter;
use FilterIterator;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\TestSuite;
use ReflectionClass;
class Factory
{
    private $filters = [];
    public function addFilter(ReflectionClass $filter, $args): void
    {
        if (!$filter->isSubclassOf(\RecursiveFilterIterator::class)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Class "%s" does not extend RecursiveFilterIterator',
                    $filter->name
                )
            );
        }
        $this->filters[] = [$filter, $args];
    }
    public function factory(Iterator $iterator, TestSuite $suite): FilterIterator
    {
        foreach ($this->filters as $filter) {
            [$class, $args] = $filter;
            $iterator       = $class->newInstance($iterator, $args, $suite);
        }
        return $iterator;
    }
}
