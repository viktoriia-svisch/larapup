<?php
use PHPUnit\Framework\TestCase;
class DataProviderTestDoxTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
    public function testDoesSomethingElse(): void
    {
        $this->assertTrue(true);
    }
    public function testWithProviderWithIndexedArray($value): void
    {
        $this->assertTrue(true);
    }
    public function testWithPlaceholders($value): void
    {
        $this->assertTrue(true);
    }
    public function provider()
    {
        return [
            'one' => [1],
            'two' => [2],
        ];
    }
    public function providerWithIndexedArray()
    {
        return [
            ['first'],
            ['second'],
        ];
    }
    public function placeHolderprovider(): array
    {
        return [
            'boolean'          => [true],
            'integer'          => [1],
            'float'            => [1.0],
            'string'           => ['string'],
            'array'            => [[1, 2, 3]],
            'object'           => [new \stdClass],
            'stringableObject' => [new class {
                public function __toString()
                {
                    return 'string';
                }
            }],
            'resource'         => [\fopen(__FILE__, 'rb')],
            'null'             => [null],
        ];
    }
}
