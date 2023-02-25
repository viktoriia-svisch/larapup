<?php
namespace Symfony\Component\Console\Input;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidOptionException;
class ArrayInput extends Input
{
    private $parameters;
    public function __construct(array $parameters, InputDefinition $definition = null)
    {
        $this->parameters = $parameters;
        parent::__construct($definition);
    }
    public function getFirstArgument()
    {
        foreach ($this->parameters as $key => $value) {
            if ($key && '-' === $key[0]) {
                continue;
            }
            return $value;
        }
    }
    public function hasParameterOption($values, $onlyParams = false)
    {
        $values = (array) $values;
        foreach ($this->parameters as $k => $v) {
            if (!\is_int($k)) {
                $v = $k;
            }
            if ($onlyParams && '--' === $v) {
                return false;
            }
            if (\in_array($v, $values)) {
                return true;
            }
        }
        return false;
    }
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        $values = (array) $values;
        foreach ($this->parameters as $k => $v) {
            if ($onlyParams && ('--' === $k || (\is_int($k) && '--' === $v))) {
                return $default;
            }
            if (\is_int($k)) {
                if (\in_array($v, $values)) {
                    return true;
                }
            } elseif (\in_array($k, $values)) {
                return $v;
            }
        }
        return $default;
    }
    public function __toString()
    {
        $params = [];
        foreach ($this->parameters as $param => $val) {
            if ($param && '-' === $param[0]) {
                if (\is_array($val)) {
                    foreach ($val as $v) {
                        $params[] = $param.('' != $v ? '='.$this->escapeToken($v) : '');
                    }
                } else {
                    $params[] = $param.('' != $val ? '='.$this->escapeToken($val) : '');
                }
            } else {
                $params[] = \is_array($val) ? implode(' ', array_map([$this, 'escapeToken'], $val)) : $this->escapeToken($val);
            }
        }
        return implode(' ', $params);
    }
    protected function parse()
    {
        foreach ($this->parameters as $key => $value) {
            if ('--' === $key) {
                return;
            }
            if (0 === strpos($key, '--')) {
                $this->addLongOption(substr($key, 2), $value);
            } elseif ('-' === $key[0]) {
                $this->addShortOption(substr($key, 1), $value);
            } else {
                $this->addArgument($key, $value);
            }
        }
    }
    private function addShortOption($shortcut, $value)
    {
        if (!$this->definition->hasShortcut($shortcut)) {
            throw new InvalidOptionException(sprintf('The "-%s" option does not exist.', $shortcut));
        }
        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }
    private function addLongOption($name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            throw new InvalidOptionException(sprintf('The "--%s" option does not exist.', $name));
        }
        $option = $this->definition->getOption($name);
        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new InvalidOptionException(sprintf('The "--%s" option requires a value.', $name));
            }
            if (!$option->isValueOptional()) {
                $value = true;
            }
        }
        $this->options[$name] = $value;
    }
    private function addArgument($name, $value)
    {
        if (!$this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        $this->arguments[$name] = $value;
    }
}
