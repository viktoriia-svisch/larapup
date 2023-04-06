<?php
namespace Prophecy\PhpDocumentor;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag\MethodTag as LegacyMethodTag;
final class LegacyClassTagRetriever implements MethodTagRetrieverInterface
{
    public function getTagList(\ReflectionClass $reflectionClass)
    {
        $phpdoc = new DocBlock($reflectionClass->getDocComment());
        return $phpdoc->getTagsByName('method');
    }
}
