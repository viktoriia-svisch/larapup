<?php
namespace Prophecy\Doubler\ClassPatch;
use Prophecy\Doubler\Generator\Node\ClassNode;
class ReflectionClassNewInstancePatch implements ClassPatchInterface
{
    public function supports(ClassNode $node)
    {
        return 'ReflectionClass' === $node->getParentClass();
    }
    public function apply(ClassNode $node)
    {
        foreach ($node->getMethod('newInstance')->getArguments() as $argument) {
            $argument->setDefault(null);
        }
    }
    public function getPriority()
    {
        return 50;
    }
}
