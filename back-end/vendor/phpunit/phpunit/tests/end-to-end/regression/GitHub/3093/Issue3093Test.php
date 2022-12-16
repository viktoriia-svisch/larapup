<?php
class Issue3093Test extends \PHPUnit\Framework\TestCase
{
    public function someDataProvider(): array
    {
        return [['some values']];
    }
    public function testFirstWithoutDependencies(): void
    {
        self::assertTrue(true);
    }
    public function testSecondThatDependsOnFirstAndDataprovider($value)
    {
        self::assertTrue(true);
    }
}
