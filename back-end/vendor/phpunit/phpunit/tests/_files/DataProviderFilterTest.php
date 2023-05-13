<?php
use PHPUnit\Framework\TestCase;
class DataProviderFilterTest extends TestCase
{
    public static function truthProvider()
    {
        return [
            [true],
            [true],
            [true],
            [true],
        ];
    }
    public static function falseProvider()
    {
        return [
            'false test'       => [false],
            'false test 2'     => [false],
            'other false test' => [false],
            'other false test2'=> [false],
        ];
    }
    public function testTrue($truth): void
    {
        $this->assertTrue($truth);
    }
    public function testFalse($false): void
    {
        $this->assertFalse($false);
    }
}
