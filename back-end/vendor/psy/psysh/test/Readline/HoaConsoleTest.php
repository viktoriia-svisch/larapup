<?php
namespace Psy\Test\Readline;
use Psy\Readline\HoaConsole;
class HoaConsoleTest extends \PHPUnit\Framework\TestCase
{
    public function testHistory()
    {
        $readline = new HoaConsole();
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
}
