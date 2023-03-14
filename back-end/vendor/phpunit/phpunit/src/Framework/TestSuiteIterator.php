<?php
namespace PHPUnit\Framework;
use RecursiveIterator;
final class TestSuiteIterator implements RecursiveIterator
{
    private $position;
    private $tests;
    public function __construct(TestSuite $testSuite)
    {
        $this->tests = $testSuite->tests();
    }
    public function rewind(): void
    {
        $this->position = 0;
    }
    public function valid(): bool
    {
        return $this->position < \count($this->tests);
    }
    public function key(): int
    {
        return $this->position;
    }
    public function current(): Test
    {
        return $this->valid() ? $this->tests[$this->position] : null;
    }
    public function next(): void
    {
        $this->position++;
    }
    public function getChildren(): self
    {
        return new self(
            $this->tests[$this->position]
        );
    }
    public function hasChildren(): bool
    {
        return $this->tests[$this->position] instanceof TestSuite;
    }
}
