<?php
namespace Prophecy\PhpDocumentor;
use phpDocumentor\Reflection\DocBlock\Tag\MethodTag as LegacyMethodTag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
final class ClassAndInterfaceTagRetriever implements MethodTagRetrieverInterface
{
    private $classRetriever;
    public function __construct(MethodTagRetrieverInterface $classRetriever = null)
    {
        if (null !== $classRetriever) {
            $this->classRetriever = $classRetriever;
            return;
        }
        $this->classRetriever = class_exists('phpDocumentor\Reflection\DocBlockFactory') && class_exists('phpDocumentor\Reflection\Types\ContextFactory')
            ? new ClassTagRetriever()
            : new LegacyClassTagRetriever()
        ;
    }
    public function getTagList(\ReflectionClass $reflectionClass)
    {
        return array_merge(
            $this->classRetriever->getTagList($reflectionClass),
            $this->getInterfacesTagList($reflectionClass)
        );
    }
    private function getInterfacesTagList(\ReflectionClass $reflectionClass)
    {
        $interfaces = $reflectionClass->getInterfaces();
        $tagList = array();
        foreach($interfaces as $interface) {
            $tagList = array_merge($tagList, $this->classRetriever->getTagList($interface));
        }
        return $tagList;
    }
}
