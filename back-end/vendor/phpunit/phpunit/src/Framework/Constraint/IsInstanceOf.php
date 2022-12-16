<?php
namespace PHPUnit\Framework\Constraint;
use ReflectionClass;
use ReflectionException;
class IsInstanceOf extends Constraint
{
    private $className;
    public function __construct(string $className)
    {
        parent::__construct();
        $this->className = $className;
    }
    public function toString(): string
    {
        return \sprintf(
            'is instance of %s "%s"',
            $this->getType(),
            $this->className
        );
    }
    protected function matches($other): bool
    {
        return $other instanceof $this->className;
    }
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%s is an instance of %s "%s"',
            $this->exporter->shortenedExport($other),
            $this->getType(),
            $this->className
        );
    }
    private function getType(): string
    {
        try {
            $reflection = new ReflectionClass($this->className);
            if ($reflection->isInterface()) {
                return 'interface';
            }
        } catch (ReflectionException $e) {
        }
        return 'class';
    }
}
