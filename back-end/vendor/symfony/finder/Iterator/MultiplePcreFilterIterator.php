<?php
namespace Symfony\Component\Finder\Iterator;
abstract class MultiplePcreFilterIterator extends \FilterIterator
{
    protected $matchRegexps = [];
    protected $noMatchRegexps = [];
    public function __construct(\Iterator $iterator, array $matchPatterns, array $noMatchPatterns)
    {
        foreach ($matchPatterns as $pattern) {
            $this->matchRegexps[] = $this->toRegex($pattern);
        }
        foreach ($noMatchPatterns as $pattern) {
            $this->noMatchRegexps[] = $this->toRegex($pattern);
        }
        parent::__construct($iterator);
    }
    protected function isAccepted($string)
    {
        foreach ($this->noMatchRegexps as $regex) {
            if (preg_match($regex, $string)) {
                return false;
            }
        }
        if ($this->matchRegexps) {
            foreach ($this->matchRegexps as $regex) {
                if (preg_match($regex, $string)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }
    protected function isRegex($str)
    {
        if (preg_match('/^(.{3,}?)[imsxuADU]*$/', $str, $m)) {
            $start = substr($m[1], 0, 1);
            $end = substr($m[1], -1);
            if ($start === $end) {
                return !preg_match('/[*?[:alnum:] \\\\]/', $start);
            }
            foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
                if ($start === $delimiters[0] && $end === $delimiters[1]) {
                    return true;
                }
            }
        }
        return false;
    }
    abstract protected function toRegex($str);
}
