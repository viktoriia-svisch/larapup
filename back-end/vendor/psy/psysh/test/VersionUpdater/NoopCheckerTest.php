<?php
namespace Psy\Test\VersionUpdater;
use Psy\Shell;
use Psy\VersionUpdater\NoopChecker;
class NoopCheckerTest extends \PHPUnit\Framework\TestCase
{
    public function testTheThings()
    {
        $checker = new NoopChecker();
        $this->assertTrue($checker->isLatest());
        $this->assertEquals(Shell::VERSION, $checker->getLatest());
    }
}
