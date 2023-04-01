<?php
namespace SebastianBergmann\Comparator;
use SebastianBergmann\Exporter\Exporter;
abstract class Comparator
{
    protected $factory;
    protected $exporter;
    public function __construct()
    {
        $this->exporter = new Exporter;
    }
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
    }
    abstract public function accepts($expected, $actual);
    abstract public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false);
}
