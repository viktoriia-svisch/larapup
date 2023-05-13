<?php
class NumericGroupAnnotationTest extends \PHPUnit\Framework\TestCase
{
    public function testTicketAnnotationSupportsNumericValue(): void
    {
        $this->assertTrue(true);
    }
    public function testGroupAnnotationSupportsNumericValue(): void
    {
        $this->assertTrue(true);
    }
    public function testDummyTestThatShouldNotRun(): void
    {
        $this->doesNotPerformAssertions();
    }
}
