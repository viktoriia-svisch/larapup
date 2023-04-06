<?php
declare(strict_types=1);
namespace PHPUnit\Framework\MockObject;
final class MockMethodSet
{
    private $methods = [];
    public function addMethods(MockMethod ...$methods): void
    {
        foreach ($methods as $method) {
            $this->methods[\strtolower($method->getName())] = $method;
        }
    }
    public function asArray(): array
    {
        return \array_values($this->methods);
    }
    public function hasMethod(string $methodName): bool
    {
        return \array_key_exists(\strtolower($methodName), $this->methods);
    }
}
