<?php
namespace Barryvdh\LaravelIdeHelper;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Tag;
class Macro extends Method
{
    public function __construct(
        $method,
        $alias,
        $class,
        $methodName = null,
        $interfaces = array()
    ) {
        parent::__construct($method, $alias, $class, $methodName, $interfaces);
    }
    protected function initPhpDoc($method)
    {
        $this->phpdoc = new DocBlock($method);
    }
    protected function initClassDefinedProperties($method, \ReflectionClass $class)
    {
        $this->namespace = $class->getNamespaceName();
        $this->declaringClassName = '\\' . ltrim($class->name, '\\');
    }
}
