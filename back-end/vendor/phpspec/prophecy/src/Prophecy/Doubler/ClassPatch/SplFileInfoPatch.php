<?php
namespace Prophecy\Doubler\ClassPatch;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
class SplFileInfoPatch implements ClassPatchInterface
{
    public function supports(ClassNode $node)
    {
        if (null === $node->getParentClass()) {
            return false;
        }
        return 'SplFileInfo' === $node->getParentClass()
            || is_subclass_of($node->getParentClass(), 'SplFileInfo')
        ;
    }
    public function apply(ClassNode $node)
    {
        if ($node->hasMethod('__construct')) {
            $constructor = $node->getMethod('__construct');
        } else {
            $constructor = new MethodNode('__construct');
            $node->addMethod($constructor);
        }
        if ($this->nodeIsDirectoryIterator($node)) {
            $constructor->setCode('return parent::__construct("' . __DIR__ . '");');
            return;
        }
        if ($this->nodeIsSplFileObject($node)) {
            $filePath = str_replace('\\','\\\\',__FILE__);
            $constructor->setCode('return parent::__construct("' . $filePath .'");');
            return;
        }
        if ($this->nodeIsSymfonySplFileInfo($node)) {
            $filePath = str_replace('\\','\\\\',__FILE__);
            $constructor->setCode('return parent::__construct("' . $filePath .'", "", "");');
            return;
        }
        $constructor->useParentCode();
    }
    public function getPriority()
    {
        return 50;
    }
    private function nodeIsDirectoryIterator(ClassNode $node)
    {
        $parent = $node->getParentClass();
        return 'DirectoryIterator' === $parent
            || is_subclass_of($parent, 'DirectoryIterator');
    }
    private function nodeIsSplFileObject(ClassNode $node)
    {
        $parent = $node->getParentClass();
        return 'SplFileObject' === $parent
            || is_subclass_of($parent, 'SplFileObject');
    }
    private function nodeIsSymfonySplFileInfo(ClassNode $node)
    {
        $parent = $node->getParentClass();
        return 'Symfony\\Component\\Finder\\SplFileInfo' === $parent;
    }
}
