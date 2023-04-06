<?php
namespace PHPUnit\Framework;
class SyntheticError extends AssertionFailedError
{
    protected $syntheticFile = '';
    protected $syntheticLine = 0;
    protected $syntheticTrace = [];
    public function __construct(string $message, int $code, string $file, int $line, array $trace)
    {
        parent::__construct($message, $code);
        $this->syntheticFile  = $file;
        $this->syntheticLine  = $line;
        $this->syntheticTrace = $trace;
    }
    public function getSyntheticFile(): string
    {
        return $this->syntheticFile;
    }
    public function getSyntheticLine(): int
    {
        return $this->syntheticLine;
    }
    public function getSyntheticTrace(): array
    {
        return $this->syntheticTrace;
    }
}
