<?php
namespace Prophecy\Doubler\ClassPatch;
use Prophecy\Doubler\Generator\Node\ClassNode;
interface ClassPatchInterface
{
    public function supports(ClassNode $node);
    public function apply(ClassNode $node);
    public function getPriority();
}
