<?php
namespace Barryvdh\Reflection\DocBlock;
class Location
{
    protected $lineNumber = 0;
    protected $columnNumber = 0;
    public function __construct(
        $lineNumber = 0,
        $columnNumber = 0
    ) {
        $this->setLineNumber($lineNumber)->setColumnNumber($columnNumber);
    }
    public function getLineNumber()
    {
        return $this->lineNumber;
    }
    public function setLineNumber($lineNumber)
    {
        $this->lineNumber = (int)$lineNumber;
        return $this;
    }
    public function getColumnNumber()
    {
        return $this->columnNumber;
    }
    public function setColumnNumber($columnNumber)
    {
        $this->columnNumber = (int)$columnNumber;
        return $this;
    }
}
