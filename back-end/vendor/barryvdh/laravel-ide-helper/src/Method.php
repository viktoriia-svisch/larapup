<?php
namespace Barryvdh\LaravelIdeHelper;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag\ReturnTag;
use Barryvdh\Reflection\DocBlock\Tag\ParamTag;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
class Method
{
    protected $phpdoc;
    protected $method;
    protected $output = '';
    protected $declaringClassName;
    protected $name;
    protected $namespace;
    protected $params = array();
    protected $params_with_default = array();
    protected $interfaces = array();
    protected $real_name;
    protected $return = null;
    protected $root;
    public function __construct($method, $alias, $class, $methodName = null, $interfaces = array())
    {
        $this->method = $method;
        $this->interfaces = $interfaces;
        $this->name = $methodName ?: $method->name;
        $this->real_name = $method->isClosure() ? $this->name : $method->name;
        $this->initClassDefinedProperties($method, $class);
        $this->root = '\\' . ltrim($class->getName(), '\\');
        $this->initPhpDoc($method);
        try {
            $this->normalizeParams($this->phpdoc);
            $this->normalizeReturn($this->phpdoc);
            $this->normalizeDescription($this->phpdoc);
        } catch (\Exception $e) {
        }
        $this->getParameters($method);
        $this->phpdoc->appendTag(Tag::createInstance('@static', $this->phpdoc));
    }
    protected function initPhpDoc($method)
    {
        $this->phpdoc = new DocBlock($method, new Context($this->namespace));
    }
    protected function initClassDefinedProperties($method, \ReflectionClass $class)
    {
        $declaringClass = $method->getDeclaringClass();
        $this->namespace = $declaringClass->getNamespaceName();
        $this->declaringClassName = '\\' . ltrim($declaringClass->name, '\\');
    }
    public function getDeclaringClass()
    {
        return $this->declaringClassName;
    }
    public function getRoot()
    {
        return $this->root;
    }
    public function isInstanceCall()
    {
        return ! ($this->method->isClosure() || $this->method->isStatic());
    }
    public function getRootMethodCall()
    {
        if ($this->isInstanceCall()) {
            return "\$instance->{$this->getRealName()}({$this->getParams()})";
        } else {
            return "{$this->getRoot()}::{$this->getRealName()}({$this->getParams()})";
        }
    }
    public function getDocComment($prefix = "\t\t")
    {
        $serializer = new DocBlockSerializer(1, $prefix);
        return $serializer->getDocComment($this->phpdoc);
    }
    public function getName()
    {
        return $this->name;
    }
    public function getRealName()
    {
        return $this->real_name;
    }
    public function getParams($implode = true)
    {
        return $implode ? implode(', ', $this->params) : $this->params;
    }
    public function getParamsWithDefault($implode = true)
    {
        return $implode ? implode(', ', $this->params_with_default) : $this->params_with_default;
    }
    protected function normalizeDescription(DocBlock $phpdoc)
    {
        $description = $phpdoc->getText();
        if (strpos($description, '{@inheritdoc}') !== false) {
            $inheritdoc = $this->getInheritDoc($this->method);
            $inheritDescription = $inheritdoc->getText();
            $description = str_replace('{@inheritdoc}', $inheritDescription, $description);
            $phpdoc->setText($description);
            $this->normalizeParams($inheritdoc);
            $this->normalizeReturn($inheritdoc);
            $inheritTags = $inheritdoc->getTags();
            if ($inheritTags) {
                foreach ($inheritTags as $tag) {
                    $tag->setDocBlock();
                    $phpdoc->appendTag($tag);
                }
            }
        }
    }
    protected function normalizeParams(DocBlock $phpdoc)
    {
        $paramTags = $phpdoc->getTagsByName('param');
        if ($paramTags) {
            foreach ($paramTags as $tag) {
                $content = $this->convertKeywords($tag->getContent());
                $tag->setContent($content);
                $content = $tag->getType() . ' ' . $tag->getVariableName() . ' ' . $tag->getDescription();
                $tag->setContent(trim($content));
            }
        }
    }
    protected function normalizeReturn(DocBlock $phpdoc)
    {
        $returnTags = $phpdoc->getTagsByName('return');
        if ($returnTags) {
            $tag = reset($returnTags);
            $returnValue = $tag->getType();
            foreach ($this->interfaces as $interface => $real) {
                $returnValue = str_replace($interface, $real, $returnValue);
            }
            $tag->setContent($returnValue . ' ' . $tag->getDescription());
            $this->return = $returnValue;
            if ($tag->getType() === '$this') {
                $tag->setType($this->root);
            }
        } else {
            $this->return = null;
        }
    }
    protected function convertKeywords($string)
    {
        $string = str_replace('\Closure', 'Closure', $string);
        $string = str_replace('Closure', '\Closure', $string);
        $string = str_replace('dynamic', 'mixed', $string);
        return $string;
    }
    public function shouldReturn()
    {
        if ($this->return !== "void" && $this->method->name !== "__construct") {
            return true;
        }
        return false;
    }
    public function getParameters($method)
    {
        $params = array();
        $paramsWithDefault = array();
        foreach ($method->getParameters() as $param) {
            $paramStr = '$' . $param->getName();
            $params[] = $paramStr;
            if ($param->isOptional()) {
                $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = 'array()';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                } elseif (is_resource($default)) {
                } else {
                    $default = "'" . trim($default) . "'";
                }
                $paramStr .= " = $default";
            }
            $paramsWithDefault[] = $paramStr;
        }
        $this->params = $params;
        $this->params_with_default = $paramsWithDefault;
    }
    protected function getInheritDoc($reflectionMethod)
    {
        $parentClass = $reflectionMethod->getDeclaringClass()->getParentClass();
        if ($parentClass) {
            $method = $parentClass->getMethod($reflectionMethod->getName());
        } else {
            $method = $reflectionMethod->getPrototype();
        }
        if ($method) {
            $namespace = $method->getDeclaringClass()->getNamespaceName();
            $phpdoc = new DocBlock($method, new Context($namespace));
            if (strpos($phpdoc->getText(), '{@inheritdoc}') !== false) {
                return $this->getInheritDoc($method);
            } else {
                return $phpdoc;
            }
        }
    }
}
