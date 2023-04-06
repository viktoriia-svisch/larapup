<?php
namespace Barryvdh\LaravelIdeHelper;
use ReflectionClass;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Tag\MethodTag;
use Illuminate\Config\Repository as ConfigRepository;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
class Alias
{
    protected $alias;
    protected $facade;
    protected $extends = null;
    protected $extendsClass = null;
    protected $extendsNamespace = null;
    protected $classType = 'class';
    protected $short;
    protected $namespace = '__root';
    protected $root = null;
    protected $classes = array();
    protected $methods = array();
    protected $usedMethods = array();
    protected $valid = false;
    protected $magicMethods = array();
    protected $interfaces = array();
    protected $phpdoc = null;
    protected $config;
    public function __construct($config, $alias, $facade, $magicMethods = array(), $interfaces = array())
    {
        $this->alias = $alias;
        $this->magicMethods = $magicMethods;
        $this->interfaces = $interfaces;
        $this->config = $config;
        $facade = '\\' . ltrim($facade, '\\');
        $this->facade = $facade;
        $this->detectRoot();
        if ((!$this->isTrait() && $this->root)) {
            $this->valid = true;
        } else {
            return;
        }
        $this->addClass($this->root);
        $this->detectFake();
        $this->detectNamespace();
        $this->detectClassType();
        $this->detectExtendsNamespace();
        if (!empty($this->namespace)) {
            $this->phpdoc = new DocBlock(new ReflectionClass($alias), new Context($this->namespace));
        }
        if ($facade === '\Illuminate\Database\Eloquent\Model') {
            $this->usedMethods = array('decrement', 'increment');
        }
    }
    public function addClass($classes)
    {
        $classes = (array)$classes;
        foreach ($classes as $class) {
            if (class_exists($class) || interface_exists($class)) {
                $this->classes[] = $class;
            } else {
                echo "Class not exists: $class\r\n";
            }
        }
    }
    public function isValid()
    {
        return $this->valid;
    }
    public function getClasstype()
    {
        return $this->classType;
    }
    public function getExtends()
    {
        return $this->extends;
    }
    public function getExtendsClass()
    {
        return $this->extendsClass;
    }
    public function getExtendsNamespace()
    {
        return $this->extendsNamespace;
    }
    public function getAlias()
    {
        return $this->alias;
    }
    public function getShortName()
    {
        return $this->short;
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    public function getMethods()
    {
        if (count($this->methods) > 0) {
            return $this->methods;
        }
        $this->addMagicMethods();
        $this->detectMethods();
        return $this->methods;
    }
    protected function detectFake()
    {
        $facade = $this->facade;
        if (!method_exists($facade, 'fake')) {
            return;
        }
        $real = $facade::getFacadeRoot();
        try {
            $facade::fake();
            $fake = $facade::getFacadeRoot();
            if ($fake !== $real) {
                $this->addClass(get_class($fake));
            }
        } finally {
            $facade::swap($real);
        }
    }
    protected function detectNamespace()
    {
        if (strpos($this->alias, '\\')) {
            $nsParts = explode('\\', $this->alias);
            $this->short = array_pop($nsParts);
            $this->namespace = implode('\\', $nsParts);
        } else {
            $this->short = $this->alias;
        }
    }
    protected function detectExtendsNamespace()
    {
        if (strpos($this->extends, '\\') !== false) {
            $nsParts = explode('\\', $this->extends);
            $this->extendsClass = array_pop($nsParts);
            $this->extendsNamespace = implode('\\', $nsParts);
        }
    }
    protected function detectClassType()
    {
        if (interface_exists($this->facade)) {
            $this->classType = 'interface';
            $this->extends = $this->facade;
        } else {
            $this->classType = 'class';
            if (class_exists($this->facade)) {
                $this->extends = $this->facade;
            }
        }
    }
    protected function detectRoot()
    {
        $facade = $this->facade;
        try {
            if (method_exists($facade, 'getFacadeRoot')) {
                $root = get_class($facade::getFacadeRoot());
            } else {
                $root = $facade;
            }
            if (!class_exists($root) && !interface_exists($root)) {
                return;
            }
            $this->root = $root;
        } catch (\PDOException $e) {
            $this->error(
                "PDOException: " . $e->getMessage() .
                "\nPlease configure your database connection correctly, or use the sqlite memory driver (-M)." .
                " Skipping $facade."
            );
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage() . "\nSkipping $facade.");
        }
    }
    protected function isTrait()
    {
        if (function_exists('trait_exists') && trait_exists($this->facade)) {
            return true;
        }
        return false;
    }
    protected function addMagicMethods()
    {
        foreach ($this->magicMethods as $magic => $real) {
            list($className, $name) = explode('::', $real);
            if (!class_exists($className) && !interface_exists($className)) {
                continue;
            }
            $method = new \ReflectionMethod($className, $name);
            $class = new \ReflectionClass($className);
            if (!in_array($magic, $this->usedMethods)) {
                if ($class !== $this->root) {
                    $this->methods[] = new Method($method, $this->alias, $class, $magic, $this->interfaces);
                }
                $this->usedMethods[] = $magic;
            }
        }
    }
    protected function detectMethods()
    {
        foreach ($this->classes as $class) {
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            if ($methods) {
                foreach ($methods as $method) {
                    if (!in_array($method->name, $this->usedMethods)) {
                        if ($this->extends !== $class && substr($method->name, 0, 2) !== '__') {
                            $this->methods[] = new Method(
                                $method,
                                $this->alias,
                                $reflection,
                                $method->name,
                                $this->interfaces
                            );
                        }
                        $this->usedMethods[] = $method->name;
                    }
                }
            }
            $traits = collect($reflection->getTraitNames());
            if ($traits->contains('Illuminate\Support\Traits\Macroable')) {
                $properties = $reflection->getStaticProperties();
                $macros = isset($properties['macros']) ? $properties['macros'] : [];
                foreach ($macros as $macro_name => $macro_func) {
                    if (!in_array($macro_name, $this->usedMethods)) {
                        $this->methods[] = new Macro(
                            $this->getMacroFunction($macro_func),
                            $this->alias,
                            $reflection,
                            $macro_name,
                            $this->interfaces
                        );
                        $this->usedMethods[] = $macro_name;
                    }
                }
            }
        }
    }
    protected function getMacroFunction($macro_func)
    {
        if (is_array($macro_func) && is_callable($macro_func)) {
            return new \ReflectionMethod($macro_func[0], $macro_func[1]);
        }
        if (is_object($macro_func) && is_callable($macro_func)) {
            return new \ReflectionMethod($macro_func, '__invoke');
        }
        return new \ReflectionFunction($macro_func);
    }
    public function getDocComment($prefix = "\t\t")
    {
        $serializer = new DocBlockSerializer(1, $prefix);
        if ($this->phpdoc) {
            if ($this->config->get('ide-helper.include_class_docblocks')) {
                if (count($this->phpdoc->getTags()) === 0) {
                    $class = new ReflectionClass($this->root);
                    $this->phpdoc = new DocBlock($class->getDocComment());
                }
            }
            $this->removeDuplicateMethodsFromPhpDoc();
            return $serializer->getDocComment($this->phpdoc);
        }
        return '';
    }
    protected function removeDuplicateMethodsFromPhpDoc()
    {
        $methodNames = array_map(function (Method $method) {
            return $method->getName();
        }, $this->getMethods());
        foreach ($this->phpdoc->getTags() as $tag) {
            if ($tag instanceof MethodTag && in_array($tag->getMethodName(), $methodNames)) {
                $this->phpdoc->deleteTag($tag);
            }
        }
    }
    protected function error($string)
    {
        echo $string . "\r\n";
    }
}
