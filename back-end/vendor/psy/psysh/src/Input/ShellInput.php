<?php
namespace Psy\Input;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\StringInput;
class ShellInput extends StringInput
{
    private $hasCodeArgument = false;
    private $tokenPairs;
    private $parsed;
    public function __construct($input)
    {
        parent::__construct($input);
        $this->tokenPairs = $this->tokenize($input);
    }
    public function bind(InputDefinition $definition)
    {
        $hasCodeArgument = false;
        if ($definition->getArgumentCount() > 0) {
            $args    = $definition->getArguments();
            $lastArg = \array_pop($args);
            foreach ($args as $arg) {
                if ($arg instanceof CodeArgument) {
                    $msg = \sprintf('Unexpected CodeArgument before the final position: %s', $arg->getName());
                    throw new \InvalidArgumentException($msg);
                }
            }
            if ($lastArg instanceof CodeArgument) {
                $hasCodeArgument = true;
            }
        }
        $this->hasCodeArgument = $hasCodeArgument;
        return parent::bind($definition);
    }
    private function tokenize($input)
    {
        $tokens = [];
        $length = \strlen($input);
        $cursor = 0;
        while ($cursor < $length) {
            if (\preg_match('/\s+/A', $input, $match, null, $cursor)) {
            } elseif (\preg_match('/([^="\'\s]+?)(=?)(' . StringInput::REGEX_QUOTED_STRING . '+)/A', $input, $match, null, $cursor)) {
                $tokens[] = [
                    $match[1] . $match[2] . \stripcslashes(\str_replace(['"\'', '\'"', '\'\'', '""'], '', \substr($match[3], 1, \strlen($match[3]) - 2))),
                    \stripcslashes(\substr($input, $cursor)),
                ];
            } elseif (\preg_match('/' . StringInput::REGEX_QUOTED_STRING . '/A', $input, $match, null, $cursor)) {
                $tokens[] = [
                    \stripcslashes(\substr($match[0], 1, \strlen($match[0]) - 2)),
                    \stripcslashes(\substr($input, $cursor)),
                ];
            } elseif (\preg_match('/' . StringInput::REGEX_STRING . '/A', $input, $match, null, $cursor)) {
                $tokens[] = [
                    \stripcslashes($match[1]),
                    \stripcslashes(\substr($input, $cursor)),
                ];
            } else {
                throw new \InvalidArgumentException(\sprintf('Unable to parse input near "... %s ..."', \substr($input, $cursor, 10)));
            }
            $cursor += \strlen($match[0]);
        }
        return $tokens;
    }
    protected function parse()
    {
        $parseOptions = true;
        $this->parsed = $this->tokenPairs;
        while (null !== $tokenPair = \array_shift($this->parsed)) {
            list($token, $rest) = $tokenPair;
            if ($parseOptions && '' === $token) {
                $this->parseShellArgument($token, $rest);
            } elseif ($parseOptions && '--' === $token) {
                $parseOptions = false;
            } elseif ($parseOptions && 0 === \strpos($token, '--')) {
                $this->parseLongOption($token);
            } elseif ($parseOptions && '-' === $token[0] && '-' !== $token) {
                $this->parseShortOption($token);
            } else {
                $this->parseShellArgument($token, $rest);
            }
        }
    }
    private function parseShellArgument($token, $rest)
    {
        $c = \count($this->arguments);
        if ($this->definition->hasArgument($c)) {
            $arg = $this->definition->getArgument($c);
            if ($arg instanceof CodeArgument) {
                $this->parsed = [];
                $this->arguments[$arg->getName()] = $rest;
            } else {
                $this->arguments[$arg->getName()] = $arg->isArray() ? [$token] : $token;
            }
            return;
        }
        if ($this->definition->hasArgument($c - 1) && $this->definition->getArgument($c - 1)->isArray()) {
            $arg = $this->definition->getArgument($c - 1);
            $this->arguments[$arg->getName()][] = $token;
            return;
        }
        $all = $this->definition->getArguments();
        if (\count($all)) {
            throw new \RuntimeException(\sprintf('Too many arguments, expected arguments "%s".', \implode('" "', \array_keys($all))));
        }
        throw new \RuntimeException(\sprintf('No arguments expected, got "%s".', $token));
    }
    private function parseShortOption($token)
    {
        $name = \substr($token, 1);
        if (\strlen($name) > 1) {
            if ($this->definition->hasShortcut($name[0]) && $this->definition->getOptionForShortcut($name[0])->acceptValue()) {
                $this->addShortOption($name[0], \substr($name, 1));
            } else {
                $this->parseShortOptionSet($name);
            }
        } else {
            $this->addShortOption($name, null);
        }
    }
    private function parseShortOptionSet($name)
    {
        $len = \strlen($name);
        for ($i = 0; $i < $len; $i++) {
            if (!$this->definition->hasShortcut($name[$i])) {
                throw new \RuntimeException(\sprintf('The "-%s" option does not exist.', $name[$i]));
            }
            $option = $this->definition->getOptionForShortcut($name[$i]);
            if ($option->acceptValue()) {
                $this->addLongOption($option->getName(), $i === $len - 1 ? null : \substr($name, $i + 1));
                break;
            } else {
                $this->addLongOption($option->getName(), null);
            }
        }
    }
    private function parseLongOption($token)
    {
        $name = \substr($token, 2);
        if (false !== $pos = \strpos($name, '=')) {
            if (0 === \strlen($value = \substr($name, $pos + 1))) {
                if (PHP_VERSION_ID < 70000 && false === $value) {
                    $value = '';
                }
                \array_unshift($this->parsed, [$value, null]);
            }
            $this->addLongOption(\substr($name, 0, $pos), $value);
        } else {
            $this->addLongOption($name, null);
        }
    }
    private function addShortOption($shortcut, $value)
    {
        if (!$this->definition->hasShortcut($shortcut)) {
            throw new \RuntimeException(\sprintf('The "-%s" option does not exist.', $shortcut));
        }
        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }
    private function addLongOption($name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            throw new \RuntimeException(\sprintf('The "--%s" option does not exist.', $name));
        }
        $option = $this->definition->getOption($name);
        if (null !== $value && !$option->acceptValue()) {
            throw new \RuntimeException(\sprintf('The "--%s" option does not accept a value.', $name));
        }
        if (\in_array($value, ['', null], true) && $option->acceptValue() && \count($this->parsed)) {
            $next = \array_shift($this->parsed);
            $nextToken = $next[0];
            if ((isset($nextToken[0]) && '-' !== $nextToken[0]) || \in_array($nextToken, ['', null], true)) {
                $value = $nextToken;
            } else {
                \array_unshift($this->parsed, $next);
            }
        }
        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new \RuntimeException(\sprintf('The "--%s" option requires a value.', $name));
            }
            if (!$option->isArray() && !$option->isValueOptional()) {
                $value = true;
            }
        }
        if ($option->isArray()) {
            $this->options[$name][] = $value;
        } else {
            $this->options[$name] = $value;
        }
    }
}
