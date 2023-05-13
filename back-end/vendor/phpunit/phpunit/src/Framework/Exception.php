<?php
namespace PHPUnit\Framework;
use PHPUnit\Util\Filter;
class Exception extends \RuntimeException implements \PHPUnit\Exception
{
    protected $serializableTrace;
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->serializableTrace = $this->getTrace();
        foreach ($this->serializableTrace as $i => $call) {
            unset($this->serializableTrace[$i]['args']);
        }
    }
    public function __toString(): string
    {
        $string = TestFailure::exceptionToString($this);
        if ($trace = Filter::getFilteredStacktrace($this)) {
            $string .= "\n" . $trace;
        }
        return $string;
    }
    public function __sleep(): array
    {
        return \array_keys(\get_object_vars($this));
    }
    public function getSerializableTrace(): array
    {
        return $this->serializableTrace;
    }
}
