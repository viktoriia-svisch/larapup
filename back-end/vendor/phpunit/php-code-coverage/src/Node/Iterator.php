<?php
namespace SebastianBergmann\CodeCoverage\Node;
final class Iterator implements \RecursiveIterator
{
    private $position;
    private $nodes;
    public function __construct(Directory $node)
    {
        $this->nodes = $node->getChildNodes();
    }
    public function rewind(): void
    {
        $this->position = 0;
    }
    public function valid(): bool
    {
        return $this->position < \count($this->nodes);
    }
    public function key(): int
    {
        return $this->position;
    }
    public function current(): AbstractNode
    {
        return $this->valid() ? $this->nodes[$this->position] : null;
    }
    public function next(): void
    {
        $this->position++;
    }
    public function getChildren(): self
    {
        return new self($this->nodes[$this->position]);
    }
    public function hasChildren(): bool
    {
        return $this->nodes[$this->position] instanceof Directory;
    }
}
