<?php
namespace Illuminate\Console;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
class Command extends SymfonyCommand
{
    use Macroable;
    protected $laravel;
    protected $input;
    protected $output;
    protected $signature;
    protected $name;
    protected $description;
    protected $hidden = false;
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;
    protected $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];
    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }
        $this->setDescription($this->description);
        $this->setHidden($this->isHidden());
        if (! isset($this->signature)) {
            $this->specifyParameters();
        }
    }
    protected function configureUsingFluentDefinition()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);
        parent::__construct($this->name = $name);
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }
    protected function specifyParameters()
    {
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }
        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->output = $this->laravel->make(
            OutputStyle::class, ['input' => $input, 'output' => $output]
        );
        return parent::run(
            $this->input = $input, $this->output
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->laravel->call([$this, 'handle']);
    }
    public function call($command, array $arguments = [])
    {
        $arguments['command'] = $command;
        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments), $this->output
        );
    }
    public function callSilent($command, array $arguments = [])
    {
        $arguments['command'] = $command;
        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments), new NullOutput
        );
    }
    protected function createInputFromArguments(array $arguments)
    {
        return tap(new ArrayInput(array_merge($this->context(), $arguments)), function ($input) {
            if ($input->hasParameterOption(['--no-interaction'], true)) {
                $input->setInteractive(false);
            }
        });
    }
    protected function context()
    {
        return collect($this->option())->only([
            'ansi',
            'no-ansi',
            'no-interaction',
            'quiet',
            'verbose',
        ])->filter()->mapWithKeys(function ($value, $key) {
            return ["--{$key}" => $value];
        })->all();
    }
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }
        return $this->input->getArgument($key);
    }
    public function arguments()
    {
        return $this->argument();
    }
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }
        return $this->input->getOption($key);
    }
    public function options()
    {
        return $this->option();
    }
    public function confirm($question, $default = false)
    {
        return $this->output->confirm($question, $default);
    }
    public function ask($question, $default = null)
    {
        return $this->output->ask($question, $default);
    }
    public function anticipate($question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);
        $question->setAutocompleterValues($choices);
        return $this->output->askQuestion($question);
    }
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);
        $question->setHidden(true)->setHiddenFallback($fallback);
        return $this->output->askQuestion($question);
    }
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($attempts)->setMultiselect($multiple);
        return $this->output->askQuestion($question);
    }
    public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $table = new Table($this->output);
        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }
        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);
        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }
        $table->render();
    }
    public function info($string, $verbosity = null)
    {
        $this->line($string, 'info', $verbosity);
    }
    public function line($string, $style = null, $verbosity = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;
        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }
    public function comment($string, $verbosity = null)
    {
        $this->line($string, 'comment', $verbosity);
    }
    public function question($string, $verbosity = null)
    {
        $this->line($string, 'question', $verbosity);
    }
    public function error($string, $verbosity = null)
    {
        $this->line($string, 'error', $verbosity);
    }
    public function warn($string, $verbosity = null)
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }
        $this->line($string, 'warning', $verbosity);
    }
    public function alert($string)
    {
        $length = Str::length(strip_tags($string)) + 12;
        $this->comment(str_repeat('*', $length));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', $length));
        $this->output->newLine();
    }
    protected function setVerbosity($level)
    {
        $this->verbosity = $this->parseVerbosity($level);
    }
    protected function parseVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (! is_int($level)) {
            $level = $this->verbosity;
        }
        return $level;
    }
    public function isHidden()
    {
        return $this->hidden;
    }
    public function setHidden($hidden)
    {
        parent::setHidden($this->hidden = $hidden);
        return $this;
    }
    protected function getArguments()
    {
        return [];
    }
    protected function getOptions()
    {
        return [];
    }
    public function getOutput()
    {
        return $this->output;
    }
    public function getLaravel()
    {
        return $this->laravel;
    }
    public function setLaravel($laravel)
    {
        $this->laravel = $laravel;
    }
}
