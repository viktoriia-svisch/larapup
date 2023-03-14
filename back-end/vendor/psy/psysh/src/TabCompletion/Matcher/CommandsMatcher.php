<?php
namespace Psy\TabCompletion\Matcher;
use Psy\Command\Command;
class CommandsMatcher extends AbstractMatcher
{
    protected $commands = [];
    public function __construct(array $commands)
    {
        $this->setCommands($commands);
    }
    public function setCommands(array $commands)
    {
        $names = [];
        foreach ($commands as $command) {
            $names = \array_merge([$command->getName()], $names);
            $names = \array_merge($command->getAliases(), $names);
        }
        $this->commands = $names;
    }
    protected function isCommand($name)
    {
        return \in_array($name, $this->commands);
    }
    protected function matchCommand($name)
    {
        foreach ($this->commands as $cmd) {
            if ($this->startsWith($name, $cmd)) {
                return true;
            }
        }
        return false;
    }
    public function getMatches(array $tokens, array $info = [])
    {
        $input = $this->getInput($tokens);
        return \array_filter($this->commands, function ($command) use ($input) {
            return AbstractMatcher::startsWith($input, $command);
        });
    }
    public function hasMatched(array $tokens)
    {
         \array_shift($tokens);
        $command = \array_shift($tokens);
        switch (true) {
            case self::tokenIs($command, self::T_STRING) &&
                !$this->isCommand($command[1]) &&
                $this->matchCommand($command[1]) &&
                empty($tokens):
                return true;
        }
        return false;
    }
}
