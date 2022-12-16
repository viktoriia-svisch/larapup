<?php
namespace Illuminate\Console\Events;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class CommandStarting
{
    public $command;
    public $input;
    public $output;
    public function __construct($command, InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->command = $command;
    }
}
