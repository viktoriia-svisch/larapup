<?php
use PHPUnit\Framework\TestCase;
class DoesNotPerformAssertionsButPerformingAssertionsTest extends TestCase
{
    public function testFalseAndTrueAreStillFine(): void
    {
        $this->assertFalse(false);
        $this->assertTrue(true);
    }
}
