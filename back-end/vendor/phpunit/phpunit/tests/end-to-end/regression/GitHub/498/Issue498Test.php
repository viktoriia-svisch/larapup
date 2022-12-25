<?php
use PHPUnit\Framework\TestCase;
class Issue498Test extends TestCase
{
    public function shouldBeTrue($testData): void
    {
        $this->assertTrue(true);
    }
    public function shouldBeFalse($testData): void
    {
        $this->assertFalse(false);
    }
    public function shouldBeTrueDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }
    public function shouldBeFalseDataProvider()
    {
        throw new Exception("Can't create the data");
        return [
            [true],
            [false],
        ];
    }
}
