<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use Psy\Exception\FatalErrorException;
class ValidConstructorPass extends CodeCleanerPass
{
    private $namespace;
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = [];
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = isset($node->name) ? $node->name->parts : [];
        } elseif ($node instanceof Class_) {
            $constructor = null;
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof ClassMethod) {
                    if ('__construct' === \strtolower($stmt->name)) {
                        $this->validateConstructor($stmt, $node);
                        return;
                    }
                    if (empty($this->namespace) && \strtolower($node->name) === \strtolower($stmt->name)) {
                        $constructor = $stmt;
                    }
                }
            }
            if ($constructor) {
                $this->validateConstructor($constructor, $node);
            }
        }
    }
    private function validateConstructor(Node $constructor, Node $classNode)
    {
        if ($constructor->isStatic()) {
            $className = $classNode->name instanceof Identifier ? $classNode->name->toString() : $classNode->name;
            $msg = \sprintf(
                'Constructor %s::%s() cannot be static',
                \implode('\\', \array_merge($this->namespace, (array) $className)),
                $constructor->name
            );
            throw new FatalErrorException($msg, 0, E_ERROR, null, $classNode->getLine());
        }
        if (\method_exists($constructor, 'getReturnType') && $constructor->getReturnType()) {
            $className = $classNode->name instanceof Identifier ? $classNode->name->toString() : $classNode->name;
            $msg = \sprintf(
                'Constructor %s::%s() cannot declare a return type',
                \implode('\\', \array_merge($this->namespace, (array) $className)),
                $constructor->name
            );
            throw new FatalErrorException($msg, 0, E_ERROR, null, $classNode->getLine());
        }
    }
}
