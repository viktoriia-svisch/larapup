<?php
namespace Psy\Test\Readline;
use Psy\Readline\Libedit;
class LibeditTest extends \PHPUnit\Framework\TestCase
{
    private $historyFile;
    public function setUp()
    {
        if (!Libedit::isSupported()) {
            $this->markTestSkipped('Libedit not enabled');
        }
        $this->historyFile = \tempnam(\sys_get_temp_dir(), 'psysh_test_history');
        if (false === \file_put_contents($this->historyFile, "_HiStOrY_V2_\n")) {
            $this->fail('Unable to write history file: ' . $this->historyFile);
        }
        \readline_read_history($this->historyFile);
        \readline_clear_history();
    }
    public function tearDown()
    {
        if (\is_file($this->historyFile)) {
            \unlink($this->historyFile);
        }
    }
    public function testHistory()
    {
        $readline = new Libedit($this->historyFile);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $this->assertSame(['foo'], $readline->listHistory());
        $readline->addHistory('bar');
        $this->assertSame(['foo', 'bar'], $readline->listHistory());
        $readline->addHistory('baz');
        $this->assertSame(['foo', 'bar', 'baz'], $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }
    public function testHistorySize()
    {
        $readline = new Libedit($this->historyFile, 2);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $readline->addHistory('bar');
        $this->assertSame(['foo', 'bar'], $readline->listHistory());
        $readline->addHistory('baz');
        $this->assertSame(['bar', 'baz'], $readline->listHistory());
        $readline->addHistory('w00t');
        $this->assertSame(['baz', 'w00t'], $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }
    public function testHistoryEraseDups()
    {
        $readline = new Libedit($this->historyFile, 0, true);
        $this->assertEmpty($readline->listHistory());
        $readline->addHistory('foo');
        $readline->addHistory('bar');
        $readline->addHistory('foo');
        $this->assertSame(['bar', 'foo'], $readline->listHistory());
        $readline->addHistory('baz');
        $readline->addHistory('w00t');
        $readline->addHistory('baz');
        $this->assertSame(['bar', 'foo', 'w00t', 'baz'], $readline->listHistory());
        $readline->clearHistory();
        $this->assertEmpty($readline->listHistory());
    }
    public function testListHistory()
    {
        $readline = new Libedit($this->historyFile);
        \file_put_contents(
            $this->historyFile,
            "This is an entry\n\0This is a comment\nThis is an entry\0With a comment\n",
            FILE_APPEND
        );
        $this->assertSame([
            'This is an entry',
            'This is an entry',
        ], $readline->listHistory());
        $readline->clearHistory();
    }
    public function testLinebreaksSupport()
    {
        $readline = new Libedit($this->historyFile);
        \file_put_contents(
            $this->historyFile,
            "foo\rbar\nbaz\r\nw00t",
            FILE_APPEND
        );
        $this->assertSame([
            "foo\rbar",
            "baz\r",
            'w00t',
        ], $readline->listHistory());
        $readline->clearHistory();
    }
}
