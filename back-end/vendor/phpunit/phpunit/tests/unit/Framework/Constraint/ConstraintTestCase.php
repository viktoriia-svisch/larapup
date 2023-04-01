<?php
namespace PHPUnit\Framework\Constraint;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestCase;
abstract class ConstraintTestCase extends TestCase
{
    final public function testIsCountable(): void
    {
        $className = $this->className();
        $reflection = new \ReflectionClass($className);
        $this->assertTrue($reflection->implementsInterface(\Countable::class), \sprintf(
            'Failed to assert that "%s" implements "%s".',
            $className,
            \Countable::class
        ));
    }
    final public function testIsSelfDescribing(): void
    {
        $className = $this->className();
        $reflection = new \ReflectionClass($className);
        $this->assertTrue($reflection->implementsInterface(SelfDescribing::class), \sprintf(
            'Failed to assert that "%s" implements "%s".',
            $className,
            SelfDescribing::class
        ));
    }
    final protected function className(): string
    {
        return \preg_replace(
            '/Test$/',
            '',
            static::class
        );
    }
}
