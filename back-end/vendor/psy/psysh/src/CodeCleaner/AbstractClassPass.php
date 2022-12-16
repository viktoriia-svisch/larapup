<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Psy\Exception\FatalErrorException;
class AbstractClassPass extends CodeCleanerPass
{
    private $class;
    private $abstractMethods;
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            $this->class = $node;
            $this->abstractMethods = [];
        } elseif ($node instanceof ClassMethod) {
            if ($node->isAbstract()) {
                $name = \sprintf('%s::%s', $this->class->name, $node->name);
                $this->abstractMethods[] = $name;
                if ($node->stmts !== null) {
                    $msg = \sprintf('Abstract function %s cannot contain body', $name);
                    throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
                }
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            $count = \count($this->abstractMethods);
            if ($count > 0 && !$node->isAbstract()) {
                $msg = \sprintf(
                    'Class %s contains %d abstract method%s must therefore be declared abstract or implement the remaining methods (%s)',
                    $node->name,
                    $count,
                    ($count === 1) ? '' : 's',
                    \implode(', ', $this->abstractMethods)
                );
                throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
            }
        }
    }
}
