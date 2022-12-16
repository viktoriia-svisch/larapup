<?php
namespace PHPUnit\Framework;
class SkippedTestCase extends TestCase
{
    protected $message = '';
    protected $backupGlobals = false;
    protected $backupStaticAttributes = false;
    protected $runTestInSeparateProcess = false;
    protected $useErrorHandler = false;
    protected $useOutputBuffering = false;
    public function __construct(string $className, string $methodName, string $message = '')
    {
        parent::__construct($className . '::' . $methodName);
        $this->message = $message;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function toString(): string
    {
        return $this->getName();
    }
    protected function runTest(): void
    {
        $this->markTestSkipped($this->message);
    }
}
