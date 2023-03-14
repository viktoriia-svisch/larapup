<?php
namespace Psy\Reflection;
class ReflectionLanguageConstructParameter extends \ReflectionParameter
{
    private $function;
    private $parameter;
    private $opts;
    public function __construct($function, $parameter, array $opts)
    {
        $this->function  = $function;
        $this->parameter = $parameter;
        $this->opts      = $opts;
    }
    public function getClass()
    {
        return;
    }
    public function isArray()
    {
        return \array_key_exists('isArray', $this->opts) && $this->opts['isArray'];
    }
    public function getDefaultValue()
    {
        if ($this->isDefaultValueAvailable()) {
            return $this->opts['defaultValue'];
        }
    }
    public function getName()
    {
        return $this->parameter;
    }
    public function isOptional()
    {
        return \array_key_exists('isOptional', $this->opts) && $this->opts['isOptional'];
    }
    public function isDefaultValueAvailable()
    {
        return \array_key_exists('defaultValue', $this->opts);
    }
    public function isPassedByReference()
    {
        return \array_key_exists('isPassedByReference', $this->opts) && $this->opts['isPassedByReference'];
    }
}
