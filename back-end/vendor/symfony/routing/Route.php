<?php
namespace Symfony\Component\Routing;
class Route implements \Serializable
{
    private $path = '/';
    private $host = '';
    private $schemes = [];
    private $methods = [];
    private $defaults = [];
    private $requirements = [];
    private $options = [];
    private $condition = '';
    private $compiled;
    public function __construct(string $path, array $defaults = [], array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $this->setPath($path);
        $this->addDefaults($defaults);
        $this->addRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
        $this->setCondition($condition);
    }
    public function serialize()
    {
        return serialize([
            'path' => $this->path,
            'host' => $this->host,
            'defaults' => $this->defaults,
            'requirements' => $this->requirements,
            'options' => $this->options,
            'schemes' => $this->schemes,
            'methods' => $this->methods,
            'condition' => $this->condition,
            'compiled' => $this->compiled,
        ]);
    }
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->path = $data['path'];
        $this->host = $data['host'];
        $this->defaults = $data['defaults'];
        $this->requirements = $data['requirements'];
        $this->options = $data['options'];
        $this->schemes = $data['schemes'];
        $this->methods = $data['methods'];
        if (isset($data['condition'])) {
            $this->condition = $data['condition'];
        }
        if (isset($data['compiled'])) {
            $this->compiled = $data['compiled'];
        }
    }
    public function getPath()
    {
        return $this->path;
    }
    public function setPath($pattern)
    {
        if (false !== strpbrk($pattern, '?<')) {
            $pattern = preg_replace_callback('#\{(\w++)(<.*?>)?(\?[^\}]*+)?\}#', function ($m) {
                if (isset($m[3][0])) {
                    $this->setDefault($m[1], '?' !== $m[3] ? substr($m[3], 1) : null);
                }
                if (isset($m[2][0])) {
                    $this->setRequirement($m[1], substr($m[2], 1, -1));
                }
                return '{'.$m[1].'}';
            }, $pattern);
        }
        $this->path = '/'.ltrim(trim($pattern), '/');
        $this->compiled = null;
        return $this;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function setHost($pattern)
    {
        $this->host = (string) $pattern;
        $this->compiled = null;
        return $this;
    }
    public function getSchemes()
    {
        return $this->schemes;
    }
    public function setSchemes($schemes)
    {
        $this->schemes = array_map('strtolower', (array) $schemes);
        $this->compiled = null;
        return $this;
    }
    public function hasScheme($scheme)
    {
        return \in_array(strtolower($scheme), $this->schemes, true);
    }
    public function getMethods()
    {
        return $this->methods;
    }
    public function setMethods($methods)
    {
        $this->methods = array_map('strtoupper', (array) $methods);
        $this->compiled = null;
        return $this;
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function setOptions(array $options)
    {
        $this->options = [
            'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler',
        ];
        return $this->addOptions($options);
    }
    public function addOptions(array $options)
    {
        foreach ($options as $name => $option) {
            $this->options[$name] = $option;
        }
        $this->compiled = null;
        return $this;
    }
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        $this->compiled = null;
        return $this;
    }
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
    public function hasOption($name)
    {
        return \array_key_exists($name, $this->options);
    }
    public function getDefaults()
    {
        return $this->defaults;
    }
    public function setDefaults(array $defaults)
    {
        $this->defaults = [];
        return $this->addDefaults($defaults);
    }
    public function addDefaults(array $defaults)
    {
        foreach ($defaults as $name => $default) {
            $this->defaults[$name] = $default;
        }
        $this->compiled = null;
        return $this;
    }
    public function getDefault($name)
    {
        return isset($this->defaults[$name]) ? $this->defaults[$name] : null;
    }
    public function hasDefault($name)
    {
        return \array_key_exists($name, $this->defaults);
    }
    public function setDefault($name, $default)
    {
        $this->defaults[$name] = $default;
        $this->compiled = null;
        return $this;
    }
    public function getRequirements()
    {
        return $this->requirements;
    }
    public function setRequirements(array $requirements)
    {
        $this->requirements = [];
        return $this->addRequirements($requirements);
    }
    public function addRequirements(array $requirements)
    {
        foreach ($requirements as $key => $regex) {
            $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        }
        $this->compiled = null;
        return $this;
    }
    public function getRequirement($key)
    {
        return isset($this->requirements[$key]) ? $this->requirements[$key] : null;
    }
    public function hasRequirement($key)
    {
        return \array_key_exists($key, $this->requirements);
    }
    public function setRequirement($key, $regex)
    {
        $this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
        $this->compiled = null;
        return $this;
    }
    public function getCondition()
    {
        return $this->condition;
    }
    public function setCondition($condition)
    {
        $this->condition = (string) $condition;
        $this->compiled = null;
        return $this;
    }
    public function compile()
    {
        if (null !== $this->compiled) {
            return $this->compiled;
        }
        $class = $this->getOption('compiler_class');
        return $this->compiled = $class::compile($this);
    }
    private function sanitizeRequirement($key, $regex)
    {
        if (!\is_string($regex)) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
        }
        if ('' !== $regex && '^' === $regex[0]) {
            $regex = (string) substr($regex, 1); 
        }
        if ('$' === substr($regex, -1)) {
            $regex = substr($regex, 0, -1);
        }
        if ('' === $regex) {
            throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
        }
        return $regex;
    }
}
