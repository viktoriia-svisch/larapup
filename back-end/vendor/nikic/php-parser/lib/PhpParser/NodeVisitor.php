<?php declare(strict_types=1);
namespace PhpParser;
interface NodeVisitor
{
    public function beforeTraverse(array $nodes);
    public function enterNode(Node $node);
    public function leaveNode(Node $node);
    public function afterTraverse(array $nodes);
}
