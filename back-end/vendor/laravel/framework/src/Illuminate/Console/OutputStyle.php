<?php
namespace Illuminate\Console;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class OutputStyle extends SymfonyStyle
{
    private $output;
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        parent::__construct($input, $output);
    }
    public function isQuiet()
    {
        return $this->output->isQuiet();
    }
    public function isVerbose()
    {
        return $this->output->isVerbose();
    }
    public function isVeryVerbose()
    {
        return $this->output->isVeryVerbose();
    }
    public function isDebug()
    {
        return $this->output->isDebug();
    }
}
