<?php
namespace Mockery\Generator;
class Parameter
{
    private static $parameterCounter;
    private $rfp;
    public function __construct(\ReflectionParameter $rfp)
    {
        $this->rfp = $rfp;
    }
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->rfp, $method), $args);
    }
    public function getClass()
    {
        return new DefinedTargetClass($this->rfp->getClass());
    }
    public function getTypeHintAsString()
    {
        if (method_exists($this->rfp, 'getTypehintText')) {
            $typehint = $this->rfp->getTypehintText();
            if (in_array($typehint, array('int', 'integer', 'float', 'string', 'bool', 'boolean'))) {
                return '';
            }
            return $typehint;
        }
        if ($this->rfp->isArray()) {
            return 'array';
        }
        if ((version_compare(PHP_VERSION, '5.4.1') >= 0)) {
            try {
                if ($this->rfp->getClass()) {
                    return $this->rfp->getClass()->getName();
                }
            } catch (\ReflectionException $re) {
            }
        }
        if (version_compare(PHP_VERSION, '7.0.0-dev') >= 0 && $this->rfp->hasType()) {
            return (string) $this->rfp->getType();
        }
        if (preg_match('/^Parameter #[0-9]+ \[ \<(required|optional)\> (?<typehint>\S+ )?.*\$' . $this->rfp->getName() . ' .*\]$/', $this->rfp->__toString(), $typehintMatch)) {
            if (!empty($typehintMatch['typehint'])) {
                return $typehintMatch['typehint'];
            }
        }
        return '';
    }
    public function getName()
    {
        $name = $this->rfp->getName();
        if (!$name || $name == '...') {
            $name = 'arg' . static::$parameterCounter++;
        }
        return $name;
    }
    public function isVariadic()
    {
        return $this->rfp->isVariadic();
    }
}
