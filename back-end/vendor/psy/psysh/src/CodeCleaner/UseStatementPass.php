<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
class UseStatementPass extends CodeCleanerPass
{
    private $aliases       = [];
    private $lastAliases   = [];
    private $lastNamespace = null;
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            if (\strtolower($node->name) === \strtolower($this->lastNamespace)) {
                $this->aliases = $this->lastAliases;
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                $alias = $use->alias ?: \end($use->name->parts);
                $this->aliases[\strtolower($alias)] = $use->name;
            }
            return NodeTraverser::REMOVE_NODE;
        } elseif ($node instanceof GroupUse) {
            foreach ($node->uses as $use) {
                $alias = $use->alias ?: \end($use->name->parts);
                $this->aliases[\strtolower($alias)] = Name::concat($node->prefix, $use->name, [
                    'startLine' => $node->prefix->getAttribute('startLine'),
                    'endLine'   => $use->name->getAttribute('endLine'),
                ]);
            }
            return NodeTraverser::REMOVE_NODE;
        } elseif ($node instanceof Namespace_) {
            $this->lastNamespace = $node->name;
            $this->lastAliases   = $this->aliases;
            $this->aliases       = [];
        } else {
            foreach ($node as $name => $subNode) {
                if ($subNode instanceof Name) {
                    if ($replacement = $this->findAlias($subNode)) {
                        $node->$name = $replacement;
                    }
                }
            }
            return $node;
        }
    }
    private function findAlias(Name $name)
    {
        $that = \strtolower($name);
        foreach ($this->aliases as $alias => $prefix) {
            if ($that === $alias) {
                return new FullyQualifiedName($prefix->toString());
            } elseif (\substr($that, 0, \strlen($alias) + 1) === $alias . '\\') {
                return new FullyQualifiedName($prefix->toString() . \substr($name, \strlen($alias)));
            }
        }
    }
}
