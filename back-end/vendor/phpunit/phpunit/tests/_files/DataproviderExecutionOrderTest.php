<?php
use PHPUnit\Framework\TestCase;
class DataproviderExecutionOrderTest extends TestCase
{
    public function testFirstTestThatAlwaysWorks()
    {
        $this->assertTrue(true);
    }
    public function testAddNumbersWithADataprovider(int $a, int $b, int $sum)
    {
        $this->assertSame($sum, $a + $b);
    }
    public function testTestInTheMiddleThatAlwaysWorks()
    {
        $this->assertTrue(true);
    }
    public function testAddMoreNumbersWithADataprovider(int $a, int $b, int $sum)
    {
        $this->assertSame($sum, $a + $b);
    }
    public function dataproviderAdditions()
    {
        return [
            '1+2=3' => [1, 2, 3],
            '2+1=3' => [2, 1, 3],
            '1+1=3' => [1, 1, 3],
        ];
    }
}
