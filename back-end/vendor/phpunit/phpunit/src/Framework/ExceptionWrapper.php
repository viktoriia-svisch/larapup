<?php
namespace PHPUnit\Framework;
use PHPUnit\Util\Filter;
use Throwable;
class ExceptionWrapper extends Exception
{
    protected $className;
    protected $previous;
    public function __construct(Throwable $t)
    {
        parent::__construct($t->getMessage(), (int) $t->getCode());
        $this->setOriginalException($t);
    }
    public function __toString(): string
    {
        $string = TestFailure::exceptionToString($this);
        if ($trace = Filter::getFilteredStacktrace($this)) {
            $string .= "\n" . $trace;
        }
        if ($this->previous) {
            $string .= "\nCaused by\n" . $this->previous;
        }
        return $string;
    }
    public function getClassName(): string
    {
        return $this->className;
    }
    public function getPreviousWrapped(): ?self
    {
        return $this->previous;
    }
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }
    public function setOriginalException(\Throwable $t): void
    {
        $this->originalException($t);
        $this->className = \get_class($t);
        $this->file      = $t->getFile();
        $this->line      = $t->getLine();
        $this->serializableTrace = $t->getTrace();
        foreach ($this->serializableTrace as $i => $call) {
            unset($this->serializableTrace[$i]['args']);
        }
        if ($t->getPrevious()) {
            $this->previous = new self($t->getPrevious());
        }
    }
    public function getOriginalException(): ?Throwable
    {
        return $this->originalException();
    }
    private function originalException(Throwable $exceptionToStore = null): ?Throwable
    {
        static $originalExceptions;
        $instanceId = \spl_object_hash($this);
        if ($exceptionToStore) {
            $originalExceptions[$instanceId] = $exceptionToStore;
        }
        return $originalExceptions[$instanceId] ?? null;
    }
}
