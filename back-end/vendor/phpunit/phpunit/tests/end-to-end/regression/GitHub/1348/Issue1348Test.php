<?php
use PHPUnit\Framework\TestCase;
class Issue1348Test extends TestCase
{
    public function testSTDOUT(): void
    {
        \fwrite(\STDOUT, "\nSTDOUT does not break test result\n");
        $this->assertTrue(true);
    }
    public function testSTDERR(): void
    {
        \fwrite(\STDERR, 'STDERR works as usual.');
    }
}
