<?php
namespace Prophecy\Doubler\ClassPatch;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
use Prophecy\PhpDocumentor\ClassAndInterfaceTagRetriever;
use Prophecy\PhpDocumentor\MethodTagRetrieverInterface;
class MagicCallPatch implements ClassPatchInterface
{
    private $tagRetriever;
    public function __construct(MethodTagRetrieverInterface $tagRetriever = null)
    {
        $this->tagRetriever = null === $tagRetriever ? new ClassAndInterfaceTagRetriever() : $tagRetriever;
    }
    public function supports(ClassNode $node)
    {
        return true;
    }
    public function apply(ClassNode $node)
    {
        $types = array_filter($node->getInterfaces(), function ($interface) {
            return 0 !== strpos($interface, 'Prophecy\\');
        });
        $types[] = $node->getParentClass();
        foreach ($types as $type) {
            $reflectionClass = new \ReflectionClass($type);
            while ($reflectionClass) {
                $tagList = $this->tagRetriever->getTagList($reflectionClass);
                foreach ($tagList as $tag) {
                    $methodName = $tag->getMethodName();
                    if (empty($methodName)) {
                        continue;
                    }
                    if (!$reflectionClass->hasMethod($methodName)) {
                        $methodNode = new MethodNode($methodName);
                        $methodNode->setStatic($tag->isStatic());
                        $node->addMethod($methodNode);
                    }
                }
                $reflectionClass = $reflectionClass->getParentClass();
            }
        }
    }
    public function getPriority()
    {
        return 50;
    }
}
