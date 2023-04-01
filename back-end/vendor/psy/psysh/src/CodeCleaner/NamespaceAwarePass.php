<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Stmt\Namespace_;
abstract class NamespaceAwarePass extends CodeCleanerPass
{
    protected $namespace;
    protected $currentScope;
    public function beforeTraverse(array $nodes)
    {
        $this->namespace    = [];
        $this->currentScope = [];
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = isset($node->name) ? $node->name->parts : [];
        }
    }
    protected function getFullyQualifiedName($name)
    {
        if ($name instanceof FullyQualifiedName) {
            return \implode('\\', $name->parts);
        } elseif ($name instanceof Name) {
            $name = $name->parts;
        } elseif (!\is_array($name)) {
            $name = [$name];
        }
        return \implode('\\', \array_merge($this->namespace, $name));
    }
}
