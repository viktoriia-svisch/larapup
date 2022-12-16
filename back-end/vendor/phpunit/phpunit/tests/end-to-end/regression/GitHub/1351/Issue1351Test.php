<?php
use PHPUnit\Framework\TestCase;
class Issue1351Test extends TestCase
{
    protected $instance;
    public function testFailurePre(): void
    {
        $this->instance = new ChildProcessClass1351;
        $this->assertFalse(true, 'Expected failure.');
    }
    public function testFailurePost(): void
    {
        $this->assertNull($this->instance);
        $this->assertFalse(\class_exists(ChildProcessClass1351::class, false), 'ChildProcessClass1351 is not loaded.');
    }
    public function testExceptionPre(): void
    {
        $this->instance = new ChildProcessClass1351;
        try {
            throw new LogicException('Expected exception.');
        } catch (LogicException $e) {
            throw new RuntimeException('Expected rethrown exception.', 0, $e);
        }
    }
    public function testExceptionPost(): void
    {
        $this->assertNull($this->instance);
        $this->assertFalse(\class_exists(ChildProcessClass1351::class, false), 'ChildProcessClass1351 is not loaded.');
    }
    public function testPhpCoreLanguageException(): void
    {
        $connection = new PDO('sqlite::memory:');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->query("DELETE FROM php_wtf WHERE exception_code = 'STRING'");
    }
}
