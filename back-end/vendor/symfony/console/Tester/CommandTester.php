<?php
namespace Symfony\Component\Console\Tester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
class CommandTester
{
    use TesterTrait;
    private $command;
    private $input;
    private $statusCode;
    public function __construct(Command $command)
    {
        $this->command = $command;
    }
    public function execute(array $input, array $options = [])
    {
        if (!isset($input['command'])
            && (null !== $application = $this->command->getApplication())
            && $application->getDefinition()->hasArgument('command')
        ) {
            $input = array_merge(['command' => $this->command->getName()], $input);
        }
        $this->input = new ArrayInput($input);
        $this->input->setStream(self::createStream($this->inputs));
        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }
        if (!isset($options['decorated'])) {
            $options['decorated'] = false;
        }
        $this->initOutput($options);
        return $this->statusCode = $this->command->run($this->input, $this->output);
    }
}