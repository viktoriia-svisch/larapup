<?php
namespace PHPUnit\Framework\Constraint;
use ArrayAccess;
class ArrayHasKey extends Constraint
{
    private $key;
    public function __construct($key)
    {
        parent::__construct();
        $this->key = $key;
    }
    public function toString(): string
    {
        return 'has the key ' . $this->exporter->export($this->key);
    }
    protected function matches($other): bool
    {
        if (\is_array($other)) {
            return \array_key_exists($this->key, $other);
        }
        if ($other instanceof ArrayAccess) {
            return $other->offsetExists($this->key);
        }
        return false;
    }
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }
}
