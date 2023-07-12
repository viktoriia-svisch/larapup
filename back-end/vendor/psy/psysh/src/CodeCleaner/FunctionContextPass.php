<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\FunctionLike;
use Psy\Exception\FatalErrorException;
class FunctionContextPass extends CodeCleanerPass
{
    private $functionDepth;
    public function beforeTraverse(array $nodes)
    {
        $this->functionDepth = 0;
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof FunctionLike) {
            $this->functionDepth++;
            return;
        }
        if ($this->functionDepth !== 0) {
            return;
        }
        if ($node instanceof Yield_) {
            $msg = 'The "yield" expression can only be used inside a function';
            throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof FunctionLike) {
            $this->functionDepth--;
        }
    }
}
