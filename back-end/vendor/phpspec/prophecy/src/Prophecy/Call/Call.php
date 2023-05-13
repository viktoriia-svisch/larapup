<?php
namespace Prophecy\Call;
use Exception;
use Prophecy\Argument\ArgumentsWildcard;
class Call
{
    private $methodName;
    private $arguments;
    private $returnValue;
    private $exception;
    private $file;
    private $line;
    private $scores;
    public function __construct($methodName, array $arguments, $returnValue,
                                Exception $exception = null, $file, $line)
    {
        $this->methodName  = $methodName;
        $this->arguments   = $arguments;
        $this->returnValue = $returnValue;
        $this->exception   = $exception;
        $this->scores      = new \SplObjectStorage();
        if ($file) {
            $this->file = $file;
            $this->line = intval($line);
        }
    }
    public function getMethodName()
    {
        return $this->methodName;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function getReturnValue()
    {
        return $this->returnValue;
    }
    public function getException()
    {
        return $this->exception;
    }
    public function getFile()
    {
        return $this->file;
    }
    public function getLine()
    {
        return $this->line;
    }
    public function getCallPlace()
    {
        if (null === $this->file) {
            return 'unknown';
        }
        return sprintf('%s:%d', $this->file, $this->line);
    }
    public function addScore(ArgumentsWildcard $wildcard, $score)
    {
        $this->scores[$wildcard] = $score;
        return $this;
    }
    public function getScore(ArgumentsWildcard $wildcard)
    {
        if (isset($this->scores[$wildcard])) {
            return $this->scores[$wildcard];
        }
        return $this->scores[$wildcard] = $wildcard->scoreArguments($this->getArguments());
    }
}
