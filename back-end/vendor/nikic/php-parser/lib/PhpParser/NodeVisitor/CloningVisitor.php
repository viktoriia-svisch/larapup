<?php declare(strict_types=1);
namespace PhpParser\NodeVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
class CloningVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $origNode) {
        $node = clone $origNode;
        $node->setAttribute('origNode', $origNode);
        return $node;
    }
}
