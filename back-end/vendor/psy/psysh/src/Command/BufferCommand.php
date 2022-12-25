<?php
namespace Psy\Command;
use Psy\Output\ShellOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class BufferCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('buffer')
            ->setAliases(['buf'])
            ->setDefinition([
                new InputOption('clear', '', InputOption::VALUE_NONE, 'Clear the current buffer.'),
            ])
            ->setDescription('Show (or clear) the contents of the code input buffer.')
            ->setHelp(
                <<<'HELP'
Show the contents of the code buffer for the current multi-line expression.
Optionally, clear the buffer by passing the <info>--clear</info> option.
HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buf = $this->getApplication()->getCodeBuffer();
        if ($input->getOption('clear')) {
            $this->getApplication()->resetCodeBuffer();
            $output->writeln($this->formatLines($buf, 'urgent'), ShellOutput::NUMBER_LINES);
        } else {
            $output->writeln($this->formatLines($buf), ShellOutput::NUMBER_LINES);
        }
    }
    protected function formatLines(array $lines, $type = 'return')
    {
        $template = \sprintf('<%s>%%s</%s>', $type, $type);
        return \array_map(function ($line) use ($template) {
            return \sprintf($template, $line);
        }, $lines);
    }
}
