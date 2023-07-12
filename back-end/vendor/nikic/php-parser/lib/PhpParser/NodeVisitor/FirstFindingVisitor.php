<?php declare(strict_types=1);
namespace PhpParser\NodeVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
class FirstFindingVisitor extends NodeVisitorAbstract
{
    protected $filterCallback;
    protected $foundNode;
    public function __construct(callable $filterCallback) {
        $this->filterCallback = $filterCallback;
    }
    public function getFoundNode() {
        return $this->foundNode;
    }
    public function beforeTraverse(array $nodes) {
        $this->foundNode = null;
        return null;
    }
    public function enterNode(Node $node) {
        $filterCallback = $this->filterCallback;
        if ($filterCallback($node)) {
            $this->foundNode = $node;
            return NodeTraverser::STOP_TRAVERSAL;
        }
        return null;
    }
}
