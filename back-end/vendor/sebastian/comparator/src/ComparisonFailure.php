<?php
namespace SebastianBergmann\Comparator;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
class ComparisonFailure extends \RuntimeException
{
    protected $expected;
    protected $actual;
    protected $expectedAsString;
    protected $actualAsString;
    protected $identical;
    protected $message;
    public function __construct($expected, $actual, $expectedAsString, $actualAsString, $identical = false, $message = '')
    {
        $this->expected         = $expected;
        $this->actual           = $actual;
        $this->expectedAsString = $expectedAsString;
        $this->actualAsString   = $actualAsString;
        $this->message          = $message;
    }
    public function getActual()
    {
        return $this->actual;
    }
    public function getExpected()
    {
        return $this->expected;
    }
    public function getActualAsString()
    {
        return $this->actualAsString;
    }
    public function getExpectedAsString()
    {
        return $this->expectedAsString;
    }
    public function getDiff()
    {
        if (!$this->actualAsString && !$this->expectedAsString) {
            return '';
        }
        $differ = new Differ(new UnifiedDiffOutputBuilder("\n--- Expected\n+++ Actual\n"));
        return $differ->diff($this->expectedAsString, $this->actualAsString);
    }
    public function toString()
    {
        return $this->message . $this->getDiff();
    }
}
