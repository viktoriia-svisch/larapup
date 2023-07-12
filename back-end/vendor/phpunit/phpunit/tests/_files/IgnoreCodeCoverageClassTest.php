<?php
use PHPUnit\Framework\TestCase;
class IgnoreCodeCoverageClassTest extends TestCase
{
    public function testReturnTrue(): void
    {
        $sut = new IgnoreCodeCoverageClass;
        $this->assertTrue($sut->returnTrue());
    }
    public function testReturnFalse(): void
    {
        $sut = new IgnoreCodeCoverageClass;
        $this->assertFalse($sut->returnFalse());
    }
}
