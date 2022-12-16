<?php
namespace Illuminate\Console\Events;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class CommandFinished
{
    public $command;
    public $input;
    public $output;
    public $exitCode;
    public function __construct($command, InputInterface $input, OutputInterface $output, $exitCode)
    {
        $this->input = $input;
        $this->output = $output;
        $this->command = $command;
        $this->exitCode = $exitCode;
    }
}
