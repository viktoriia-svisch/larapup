<?php
namespace Symfony\Component\Console\Tests\Fixtures;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
class DescriptorCommandMbString extends Command
{
    protected function configure()
    {
        $this
            ->setName('descriptor:åèä')
            ->setDescription('command åèä description')
            ->setHelp('command åèä help')
            ->addUsage('-o|--option_name <argument_name>')
            ->addUsage('<argument_name>')
            ->addArgument('argument_åèä', InputArgument::REQUIRED)
            ->addOption('option_åèä', 'o', InputOption::VALUE_NONE)
        ;
    }
}
