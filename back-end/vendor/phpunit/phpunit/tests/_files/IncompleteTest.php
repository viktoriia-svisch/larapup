<?php
use PHPUnit\Framework\TestCase;
class IncompleteTest extends TestCase
{
    public function testIncomplete(): void
    {
        $this->markTestIncomplete('Test incomplete');
    }
}
