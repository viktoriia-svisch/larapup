<?php
use PHPUnit\Framework\TestCase;
class DummyFooTest extends TestCase
{
    public function testFooEqualsFoo(): void
    {
        $this->assertEquals('Foo', 'Foo');
    }
}
