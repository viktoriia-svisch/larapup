<?php
namespace Psy\Input;
use Psy\Exception\ErrorException;
use Psy\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
class FilterOptions
{
    private $filter = false;
    private $pattern;
    private $insensitive;
    private $invert;
    public static function getOptions()
    {
        return [
            new InputOption('grep',        'G', InputOption::VALUE_REQUIRED, 'Limit to items matching the given pattern (string or regex).'),
            new InputOption('insensitive', 'i', InputOption::VALUE_NONE,     'Case-insensitive search (requires --grep).'),
            new InputOption('invert',      'v', InputOption::VALUE_NONE,     'Inverted search (requires --grep).'),
        ];
    }
    public function bind(InputInterface $input)
    {
        $this->validateInput($input);
        if (!$pattern = $input->getOption('grep')) {
            $this->filter = false;
            return;
        }
        if (!$this->stringIsRegex($pattern)) {
            $pattern = '/' . \preg_quote($pattern, '/') . '/';
        }
        if ($insensitive = $input->getOption('insensitive')) {
            $pattern .= 'i';
        }
        $this->validateRegex($pattern);
        $this->filter      = true;
        $this->pattern     = $pattern;
        $this->insensitive = $insensitive;
        $this->invert      = $input->getOption('invert');
    }
    public function hasFilter()
    {
        return $this->filter;
    }
    public function match($string, array &$matches = null)
    {
        return $this->filter === false || (\preg_match($this->pattern, $string, $matches) xor $this->invert);
    }
    private function validateInput(InputInterface $input)
    {
        if (!$input->getOption('grep')) {
            foreach (['invert', 'insensitive'] as $option) {
                if ($input->getOption($option)) {
                    throw new RuntimeException('--' . $option . ' does not make sense without --grep');
                }
            }
        }
    }
    private function stringIsRegex($string)
    {
        return \substr($string, 0, 1) === '/' && \substr($string, -1) === '/' && \strlen($string) >= 3;
    }
    private function validateRegex($pattern)
    {
        \set_error_handler(['Psy\Exception\ErrorException', 'throwException']);
        try {
            \preg_match($pattern, '');
        } catch (ErrorException $e) {
            \restore_error_handler();
            throw new RuntimeException(\str_replace('preg_match(): ', 'Invalid regular expression: ', $e->getRawMessage()));
        }
        \restore_error_handler();
    }
}
