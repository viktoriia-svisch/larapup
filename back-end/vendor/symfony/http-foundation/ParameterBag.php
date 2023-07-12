<?php
namespace Symfony\Component\HttpFoundation;
class ParameterBag implements \IteratorAggregate, \Countable
{
    protected $parameters;
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }
    public function all()
    {
        return $this->parameters;
    }
    public function keys()
    {
        return array_keys($this->parameters);
    }
    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
    }
    public function add(array $parameters = [])
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }
    public function get($key, $default = null)
    {
        return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }
    public function has($key)
    {
        return \array_key_exists($key, $this->parameters);
    }
    public function remove($key)
    {
        unset($this->parameters[$key]);
    }
    public function getAlpha($key, $default = '')
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }
    public function getAlnum($key, $default = '')
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }
    public function getDigits($key, $default = '')
    {
        return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }
    public function getInt($key, $default = 0)
    {
        return (int) $this->get($key, $default);
    }
    public function getBoolean($key, $default = false)
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }
    public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        $value = $this->get($key, $default);
        if (!\is_array($options) && $options) {
            $options = ['flags' => $options];
        }
        if (\is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }
        return filter_var($value, $filter, $options);
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->parameters);
    }
    public function count()
    {
        return \count($this->parameters);
    }
}
