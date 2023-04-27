<?php
namespace PHPUnit\Framework\Constraint;
use Countable;
use Generator;
use Iterator;
use IteratorAggregate;
use Traversable;
class Count extends Constraint
{
    private $expectedCount;
    public function __construct(int $expected)
    {
        parent::__construct();
        $this->expectedCount = $expected;
    }
    public function toString(): string
    {
        return \sprintf(
            'count matches %d',
            $this->expectedCount
        );
    }
    protected function matches($other): bool
    {
        return $this->expectedCount === $this->getCountOf($other);
    }
    protected function getCountOf($other): ?int
    {
        if ($other instanceof Countable || \is_array($other)) {
            return \count($other);
        }
        if ($other instanceof Traversable) {
            while ($other instanceof IteratorAggregate) {
                $other = $other->getIterator();
            }
            $iterator = $other;
            if ($iterator instanceof Generator) {
                return $this->getCountOfGenerator($iterator);
            }
            if (!$iterator instanceof Iterator) {
                return \iterator_count($iterator);
            }
            $key   = $iterator->key();
            $count = \iterator_count($iterator);
            if ($key !== null) {
                $iterator->rewind();
                while ($iterator->valid() && $key !== $iterator->key()) {
                    $iterator->next();
                }
            }
            return $count;
        }
    }
    protected function getCountOfGenerator(Generator $generator): int
    {
        for ($count = 0; $generator->valid(); $generator->next()) {
            ++$count;
        }
        return $count;
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            'actual size %d matches expected size %d',
            $this->getCountOf($other),
            $this->expectedCount
        );
    }
}
