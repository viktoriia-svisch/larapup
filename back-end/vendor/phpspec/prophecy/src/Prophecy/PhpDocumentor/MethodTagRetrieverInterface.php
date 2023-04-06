<?php
namespace Prophecy\PhpDocumentor;
use phpDocumentor\Reflection\DocBlock\Tag\MethodTag as LegacyMethodTag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
interface MethodTagRetrieverInterface
{
    public function getTagList(\ReflectionClass $reflectionClass);
}
