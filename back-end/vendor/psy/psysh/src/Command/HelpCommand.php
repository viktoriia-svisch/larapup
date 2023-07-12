<?php
namespace Psy\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class HelpCommand extends Command
{
    private $command;
    protected function configure()
    {
        $this
            ->setName('help')
            ->setAliases(['?'])
            ->setDefinition([
                new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name.', null),
            ])
            ->setDescription('Show a list of commands. Type `help [foo]` for information about [foo].')
            ->setHelp('My. How meta.');
    }
    public function setCommand($command)
    {
        $this->command = $command;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->command !== null) {
            $output->page($this->command->asText());
            $this->command = null;
        } elseif ($name = $input->getArgument('command_name')) {
            $output->page($this->getApplication()->get($name)->asText());
        } else {
            $commands = $this->getApplication()->all();
            $table = $this->getTable($output);
            foreach ($commands as $name => $command) {
                if ($name !== $command->getName()) {
                    continue;
                }
                if ($command->getAliases()) {
                    $aliases = \sprintf('<comment>Aliases:</comment> %s', \implode(', ', $command->getAliases()));
                } else {
                    $aliases = '';
                }
                $table->addRow([
                    \sprintf('<info>%s</info>', $name),
                    $command->getDescription(),
                    $aliases,
                ]);
            }
            $output->startPaging();
            if ($table instanceof TableHelper) {
                $table->render($output);
            } else {
                $table->render();
            }
            $output->stopPaging();
        }
    }
}
