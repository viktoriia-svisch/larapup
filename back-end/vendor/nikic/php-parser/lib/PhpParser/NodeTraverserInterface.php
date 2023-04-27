<?php declare(strict_types=1);
namespace PhpParser;
interface NodeTraverserInterface
{
    public function addVisitor(NodeVisitor $visitor);
    public function removeVisitor(NodeVisitor $visitor);
    public function traverse(array $nodes) : array;
}
