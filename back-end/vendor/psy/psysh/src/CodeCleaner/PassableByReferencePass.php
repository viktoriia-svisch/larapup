<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use Psy\Exception\FatalErrorException;
class PassableByReferencePass extends CodeCleanerPass
{
    const EXCEPTION_MESSAGE = 'Only variables can be passed by reference';
    public function enterNode(Node $node)
    {
        if ($node instanceof FuncCall) {
            if ($node->name instanceof Expr || $node->name instanceof Variable) {
                return;
            }
            $name = (string) $node->name;
            if ($name === 'array_multisort') {
                return $this->validateArrayMultisort($node);
            }
            try {
                $refl = new \ReflectionFunction($name);
            } catch (\ReflectionException $e) {
                return;
            }
            foreach ($refl->getParameters() as $key => $param) {
                if (\array_key_exists($key, $node->args)) {
                    $arg = $node->args[$key];
                    if ($param->isPassedByReference() && !$this->isPassableByReference($arg)) {
                        throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, E_ERROR, null, $node->getLine());
                    }
                }
            }
        }
    }
    private function isPassableByReference(Node $arg)
    {
        return $arg->value instanceof ClassConstFetch ||
            $arg->value instanceof PropertyFetch ||
            $arg->value instanceof Variable ||
            $arg->value instanceof FuncCall ||
            $arg->value instanceof MethodCall ||
            $arg->value instanceof StaticCall;
    }
    private function validateArrayMultisort(Node $node)
    {
        $nonPassable = 2; 
        foreach ($node->args as $arg) {
            if ($this->isPassableByReference($arg)) {
                $nonPassable = 0;
            } elseif (++$nonPassable > 2) {
                throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, E_ERROR, null, $node->getLine());
            }
        }
    }
}
