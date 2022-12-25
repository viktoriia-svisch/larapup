<?php
namespace PHPUnit\Framework;
class DataProviderTestSuite extends TestSuite
{
    private $dependencies = [];
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
        foreach ($this->tests as $test) {
            $test->setDependencies($dependencies);
        }
    }
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
    public function hasDependencies(): bool
    {
        return \count($this->dependencies) > 0;
    }
}
