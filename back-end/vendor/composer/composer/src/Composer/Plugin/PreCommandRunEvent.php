<?php
namespace Composer\Plugin;
use Composer\EventDispatcher\Event;
use Symfony\Component\Console\Input\InputInterface;
class PreCommandRunEvent extends Event
{
    private $input;
    private $command;
    public function __construct($name, InputInterface $input, $command)
    {
        parent::__construct($name);
        $this->input = $input;
        $this->command = $command;
    }
    public function getInput()
    {
        return $this->input;
    }
    public function getCommand()
    {
        return $this->command;
    }
}
