<?php
namespace Psy\Input;
class SilentInput
{
    private $inputString;
    public function __construct($inputString)
    {
        $this->inputString = $inputString;
    }
    public function __toString()
    {
        return $this->inputString;
    }
}
