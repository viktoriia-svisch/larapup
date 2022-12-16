<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Unset_;
use Psy\Exception\FatalErrorException;
class FunctionReturnInWriteContextPass extends CodeCleanerPass
{
    const PHP55_MESSAGE = 'Cannot use isset() on the result of a function call (you can use "null !== func()" instead)';
    const EXCEPTION_MESSAGE = "Can't use function return value in write context";
    private $atLeastPhp55;
    public function __construct()
    {
        $this->atLeastPhp55 = \version_compare(PHP_VERSION, '5.5', '>=');
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Array_ || $this->isCallNode($node)) {
            $items = $node instanceof Array_ ? $node->items : $node->args;
            foreach ($items as $item) {
                if ($item && $item->byRef && $this->isCallNode($item->value)) {
                    throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, E_ERROR, null, $node->getLine());
                }
            }
        } elseif ($node instanceof Isset_ || $node instanceof Unset_) {
            foreach ($node->vars as $var) {
                if (!$this->isCallNode($var)) {
                    continue;
                }
                $msg = ($node instanceof Isset_ && $this->atLeastPhp55) ? self::PHP55_MESSAGE : self::EXCEPTION_MESSAGE;
                throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
            }
        } elseif ($node instanceof Empty_ && !$this->atLeastPhp55 && $this->isCallNode($node->expr)) {
            throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, E_ERROR, null, $node->getLine()); 
        } elseif ($node instanceof Assign && $this->isCallNode($node->var)) {
            throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, E_ERROR, null, $node->getLine());
        }
    }
    private function isCallNode(Node $node)
    {
        return $node instanceof FuncCall || $node instanceof MethodCall || $node instanceof StaticCall;
    }
}
