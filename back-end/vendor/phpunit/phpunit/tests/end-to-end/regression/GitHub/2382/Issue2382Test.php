<?php
use PHPUnit\Framework\TestCase;
class Issue2382Test extends TestCase
{
    public function testOne($test): void
    {
        $this->assertInstanceOf(\Exception::class, $test);
    }
    public function dataProvider()
    {
        return [
            [
                $this->getMockBuilder(\Exception::class)->getMock(),
            ],
        ];
    }
}
