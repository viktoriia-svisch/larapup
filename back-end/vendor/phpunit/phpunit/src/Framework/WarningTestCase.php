<?php
namespace PHPUnit\Framework;
class WarningTestCase extends TestCase
{
    protected $message = '';
    protected $backupGlobals = false;
    protected $backupStaticAttributes = false;
    protected $runTestInSeparateProcess = false;
    protected $useErrorHandler = false;
    public function __construct($message = '')
    {
        $this->message = $message;
        parent::__construct('Warning');
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function toString(): string
    {
        return 'Warning';
    }
    protected function runTest(): void
    {
        throw new Warning($this->message);
    }
}
