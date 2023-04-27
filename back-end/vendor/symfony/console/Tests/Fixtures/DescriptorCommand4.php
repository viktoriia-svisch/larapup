<?php
namespace Symfony\Component\Console\Tests\Fixtures;
use Symfony\Component\Console\Command\Command;
class DescriptorCommand4 extends Command
{
    protected function configure()
    {
        $this
            ->setName('descriptor:command4')
            ->setAliases(['descriptor:alias_command4', 'command4:descriptor'])
        ;
    }
}
