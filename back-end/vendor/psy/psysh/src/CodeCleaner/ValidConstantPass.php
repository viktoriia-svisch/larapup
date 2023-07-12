<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use Psy\Exception\FatalErrorException;
class ValidConstantPass extends NamespaceAwarePass
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof ConstFetch && \count($node->name->parts) > 1) {
            $name = $this->getFullyQualifiedName($node->name);
            if (!\defined($name)) {
                $msg = \sprintf('Undefined constant %s', $name);
                throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
            }
        } elseif ($node instanceof ClassConstFetch) {
            $this->validateClassConstFetchExpression($node);
        }
    }
    protected function validateClassConstFetchExpression(ClassConstFetch $stmt)
    {
        $constName = $stmt->name instanceof Identifier ? $stmt->name->toString() : $stmt->name;
        if ($constName === 'class') {
            return;
        }
        if (!$stmt->class instanceof Expr) {
            $className = $this->getFullyQualifiedName($stmt->class);
            if (\class_exists($className) || \interface_exists($className)) {
                $refl = new \ReflectionClass($className);
                if (!$refl->hasConstant($constName)) {
                    $constType = \class_exists($className) ? 'Class' : 'Interface';
                    $msg = \sprintf('%s constant \'%s::%s\' not found', $constType, $className, $constName);
                    throw new FatalErrorException($msg, 0, E_ERROR, null, $stmt->getLine());
                }
            }
        }
    }
}
