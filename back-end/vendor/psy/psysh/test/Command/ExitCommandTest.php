<?php
namespace Symfony\Component\Console\Tests\Command;
use Psy\Command\ExitCommand;
use Symfony\Component\Console\Tester\CommandTester;
class ExitCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $command = new ExitCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
    }
}
