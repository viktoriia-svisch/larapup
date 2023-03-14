<?php
namespace Symfony\Component\Console\Input;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
abstract class Input implements InputInterface, StreamableInputInterface
{
    protected $definition;
    protected $stream;
    protected $options = [];
    protected $arguments = [];
    protected $interactive = true;
    public function __construct(InputDefinition $definition = null)
    {
        if (null === $definition) {
            $this->definition = new InputDefinition();
        } else {
            $this->bind($definition);
            $this->validate();
        }
    }
    public function bind(InputDefinition $definition)
    {
        $this->arguments = [];
        $this->options = [];
        $this->definition = $definition;
        $this->parse();
    }
    abstract protected function parse();
    public function validate()
    {
        $definition = $this->definition;
        $givenArguments = $this->arguments;
        $missingArguments = array_filter(array_keys($definition->getArguments()), function ($argument) use ($definition, $givenArguments) {
            return !\array_key_exists($argument, $givenArguments) && $definition->getArgument($argument)->isRequired();
        });
        if (\count($missingArguments) > 0) {
            throw new RuntimeException(sprintf('Not enough arguments (missing: "%s").', implode(', ', $missingArguments)));
        }
    }
    public function isInteractive()
    {
        return $this->interactive;
    }
    public function setInteractive($interactive)
    {
        $this->interactive = (bool) $interactive;
    }
    public function getArguments()
    {
        return array_merge($this->definition->getArgumentDefaults(), $this->arguments);
    }
    public function getArgument($name)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        return isset($this->arguments[$name]) ? $this->arguments[$name] : $this->definition->getArgument($name)->getDefault();
    }
    public function setArgument($name, $value)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        $this->arguments[$name] = $value;
    }
    public function hasArgument($name)
    {
        return $this->definition->hasArgument($name);
    }
    public function getOptions()
    {
        return array_merge($this->definition->getOptionDefaults(), $this->options);
    }
    public function getOption($name)
    {
        if (!$this->definition->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
        }
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $this->definition->getOption($name)->getDefault();
    }
    public function setOption($name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" option does not exist.', $name));
        }
        $this->options[$name] = $value;
    }
    public function hasOption($name)
    {
        return $this->definition->hasOption($name);
    }
    public function escapeToken($token)
    {
        return preg_match('{^[\w-]+$}', $token) ? $token : escapeshellarg($token);
    }
    public function setStream($stream)
    {
        $this->stream = $stream;
    }
    public function getStream()
    {
        return $this->stream;
    }
}
