<?php
use PHPUnit\Framework\TestCase;
class Issue578Test extends TestCase
{
    public function testNoticesDoublePrintStackTrace(): void
    {
        $this->iniSet('error_reporting', \E_ALL | \E_NOTICE);
        \trigger_error('Stack Trace Test Notice', \E_NOTICE);
    }
    public function testWarningsDoublePrintStackTrace(): void
    {
        $this->iniSet('error_reporting', \E_ALL | \E_NOTICE);
        \trigger_error('Stack Trace Test Notice', \E_WARNING);
    }
    public function testUnexpectedExceptionsPrintsCorrectly(): void
    {
        throw new Exception('Double printed exception');
    }
}
