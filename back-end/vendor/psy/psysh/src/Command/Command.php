<?php
namespace Psy\Command;
use Psy\Shell;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;
abstract class Command extends BaseCommand
{
    public function setApplication(Application $application = null)
    {
        if ($application !== null && !$application instanceof Shell) {
            throw new \InvalidArgumentException('PsySH Commands require an instance of Psy\Shell');
        }
        return parent::setApplication($application);
    }
    public function asText()
    {
        $messages = [
            '<comment>Usage:</comment>',
            ' ' . $this->getSynopsis(),
            '',
        ];
        if ($this->getAliases()) {
            $messages[] = $this->aliasesAsText();
        }
        if ($this->getArguments()) {
            $messages[] = $this->argumentsAsText();
        }
        if ($this->getOptions()) {
            $messages[] = $this->optionsAsText();
        }
        if ($help = $this->getProcessedHelp()) {
            $messages[] = '<comment>Help:</comment>';
            $messages[] = ' ' . \str_replace("\n", "\n ", $help) . "\n";
        }
        return \implode("\n", $messages);
    }
    private function getArguments()
    {
        $hidden = $this->getHiddenArguments();
        return \array_filter($this->getNativeDefinition()->getArguments(), function ($argument) use ($hidden) {
            return !\in_array($argument->getName(), $hidden);
        });
    }
    protected function getHiddenArguments()
    {
        return ['command'];
    }
    private function getOptions()
    {
        $hidden = $this->getHiddenOptions();
        return \array_filter($this->getNativeDefinition()->getOptions(), function ($option) use ($hidden) {
            return !\in_array($option->getName(), $hidden);
        });
    }
    protected function getHiddenOptions()
    {
        return ['verbose'];
    }
    private function aliasesAsText()
    {
        return '<comment>Aliases:</comment> <info>' . \implode(', ', $this->getAliases()) . '</info>' . PHP_EOL;
    }
    private function argumentsAsText()
    {
        $max = $this->getMaxWidth();
        $messages = [];
        $arguments = $this->getArguments();
        if (!empty($arguments)) {
            $messages[] = '<comment>Arguments:</comment>';
            foreach ($arguments as $argument) {
                if (null !== $argument->getDefault() && (!\is_array($argument->getDefault()) || \count($argument->getDefault()))) {
                    $default = \sprintf('<comment> (default: %s)</comment>', $this->formatDefaultValue($argument->getDefault()));
                } else {
                    $default = '';
                }
                $description = \str_replace("\n", "\n" . \str_pad('', $max + 2, ' '), $argument->getDescription());
                $messages[] = \sprintf(" <info>%-${max}s</info> %s%s", $argument->getName(), $description, $default);
            }
            $messages[] = '';
        }
        return \implode(PHP_EOL, $messages);
    }
    private function optionsAsText()
    {
        $max = $this->getMaxWidth();
        $messages = [];
        $options = $this->getOptions();
        if ($options) {
            $messages[] = '<comment>Options:</comment>';
            foreach ($options as $option) {
                if ($option->acceptValue() && null !== $option->getDefault() && (!\is_array($option->getDefault()) || \count($option->getDefault()))) {
                    $default = \sprintf('<comment> (default: %s)</comment>', $this->formatDefaultValue($option->getDefault()));
                } else {
                    $default = '';
                }
                $multiple = $option->isArray() ? '<comment> (multiple values allowed)</comment>' : '';
                $description = \str_replace("\n", "\n" . \str_pad('', $max + 2, ' '), $option->getDescription());
                $optionMax = $max - \strlen($option->getName()) - 2;
                $messages[] = \sprintf(
                    " <info>%s</info> %-${optionMax}s%s%s%s",
                    '--' . $option->getName(),
                    $option->getShortcut() ? \sprintf('(-%s) ', $option->getShortcut()) : '',
                    $description,
                    $default,
                    $multiple
                );
            }
            $messages[] = '';
        }
        return \implode(PHP_EOL, $messages);
    }
    private function getMaxWidth()
    {
        $max = 0;
        foreach ($this->getOptions() as $option) {
            $nameLength = \strlen($option->getName()) + 2;
            if ($option->getShortcut()) {
                $nameLength += \strlen($option->getShortcut()) + 3;
            }
            $max = \max($max, $nameLength);
        }
        foreach ($this->getArguments() as $argument) {
            $max = \max($max, \strlen($argument->getName()));
        }
        return ++$max;
    }
    private function formatDefaultValue($default)
    {
        if (\is_array($default) && $default === \array_values($default)) {
            return \sprintf("array('%s')", \implode("', '", $default));
        }
        return \str_replace("\n", '', \var_export($default, true));
    }
    protected function getTable(OutputInterface $output)
    {
        if (!\class_exists('Symfony\Component\Console\Helper\Table')) {
            return $this->getTableHelper();
        }
        $style = new TableStyle();
        $style
            ->setVerticalBorderChar(' ')
            ->setHorizontalBorderChar('')
            ->setCrossingChar('');
        $table = new Table($output);
        return $table
            ->setRows([])
            ->setStyle($style);
    }
    protected function getTableHelper()
    {
        $table = $this->getApplication()->getHelperSet()->get('table');
        return $table
            ->setRows([])
            ->setLayout(TableHelper::LAYOUT_BORDERLESS)
            ->setHorizontalBorderChar('')
            ->setCrossingChar('');
    }
}
