<?php
namespace Psy\Command\TimeitCommand;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use Psy\CodeCleaner\NoReturnValue;
class TimeitVisitor extends NodeVisitorAbstract
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
        if ($this->functionDepth === 0 && $node instanceof Return_) {
            return new Return_($this->getEndCall($node->expr), $node->getAttributes());
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof FunctionLike) {
            $this->functionDepth--;
        }
    }
    public function afterTraverse(array $nodes)
    {
        \array_unshift($nodes, $this->maybeExpression($this->getStartCall()));
        $last = $nodes[\count($nodes) - 1];
        if ($last instanceof Expr) {
            \array_pop($nodes);
            $nodes[] = $this->getEndCall($last);
        } elseif ($last instanceof Expression) {
            \array_pop($nodes);
            $nodes[] = new Expression($this->getEndCall($last->expr), $last->getAttributes());
        } elseif ($last instanceof Return_) {
        } else {
            $nodes[] = $this->maybeExpression($this->getEndCall());
        }
        return $nodes;
    }
    private function getStartCall()
    {
        return new StaticCall(new FullyQualifiedName('Psy\Command\TimeitCommand'), 'markStart');
    }
    private function getEndCall(Expr $arg = null)
    {
        if ($arg === null) {
            $arg = NoReturnValue::create();
        }
        return new StaticCall(new FullyQualifiedName('Psy\Command\TimeitCommand'), 'markEnd', [new Arg($arg)]);
    }
    private function maybeExpression($expr, $attrs = [])
    {
        return \class_exists('PhpParser\Node\Stmt\Expression') ? new Expression($expr, $attrs) : $expr;
    }
}
