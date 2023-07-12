<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use Psy\Exception\FatalErrorException;
class ValidFunctionNamePass extends NamespaceAwarePass
{
    private $conditionalScopes = 0;
    public function enterNode(Node $node)
    {
        parent::enterNode($node);
        if (self::isConditional($node)) {
            $this->conditionalScopes++;
        } elseif ($node instanceof Function_) {
            $name = $this->getFullyQualifiedName($node->name);
            if ($this->conditionalScopes === 0) {
                if (\function_exists($name) ||
                    isset($this->currentScope[\strtolower($name)])) {
                    $msg = \sprintf('Cannot redeclare %s()', $name);
                    throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
                }
            }
            $this->currentScope[\strtolower($name)] = true;
        }
    }
    public function leaveNode(Node $node)
    {
        if (self::isConditional($node)) {
            $this->conditionalScopes--;
        } elseif ($node instanceof FuncCall) {
            $name = $node->name;
            if (!$name instanceof Expr && !$name instanceof Variable) {
                $shortName = \implode('\\', $name->parts);
                $fullName  = $this->getFullyQualifiedName($name);
                $inScope   = isset($this->currentScope[\strtolower($fullName)]);
                if (!$inScope && !\function_exists($shortName) && !\function_exists($fullName)) {
                    $message = \sprintf('Call to undefined function %s()', $name);
                    throw new FatalErrorException($message, 0, E_ERROR, null, $node->getLine());
                }
            }
        }
    }
    private static function isConditional(Node $node)
    {
        return $node instanceof If_ ||
            $node instanceof While_ ||
            $node instanceof Do_ ||
            $node instanceof Switch_;
    }
}
