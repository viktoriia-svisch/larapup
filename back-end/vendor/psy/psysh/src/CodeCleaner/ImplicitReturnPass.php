<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
class ImplicitReturnPass extends CodeCleanerPass
{
    public function beforeTraverse(array $nodes)
    {
        return $this->addImplicitReturn($nodes);
    }
    private function addImplicitReturn(array $nodes)
    {
        if (empty($nodes)) {
            return [new Return_(NoReturnValue::create())];
        }
        $last = \end($nodes);
        if ($last instanceof If_) {
            $last->stmts = $this->addImplicitReturn($last->stmts);
            foreach ($last->elseifs as $elseif) {
                $elseif->stmts = $this->addImplicitReturn($elseif->stmts);
            }
            if ($last->else) {
                $last->else->stmts = $this->addImplicitReturn($last->else->stmts);
            }
        } elseif ($last instanceof Switch_) {
            foreach ($last->cases as $case) {
                $caseLast = \end($case->stmts);
                if ($caseLast instanceof Break_) {
                    $case->stmts = $this->addImplicitReturn(\array_slice($case->stmts, 0, -1));
                    $case->stmts[] = $caseLast;
                }
            }
        } elseif ($last instanceof Expr && !($last instanceof Exit_)) {
            $nodes[\count($nodes) - 1] = new Return_($last, [
                'startLine' => $last->getLine(),
                'endLine'   => $last->getLine(),
            ]);
        } elseif ($last instanceof Expression && !($last->expr instanceof Exit_)) {
            $nodes[\count($nodes) - 1] = new Return_($last->expr, [
                'startLine' => $last->getLine(),
                'endLine'   => $last->getLine(),
            ]);
        } elseif ($last instanceof Namespace_) {
            $last->stmts = $this->addImplicitReturn($last->stmts);
        }
        if (self::isNonExpressionStmt($last)) {
            $nodes[] = new Return_(NoReturnValue::create());
        }
        return $nodes;
    }
    private static function isNonExpressionStmt(Node $node)
    {
        return $node instanceof Stmt &&
            !$node instanceof Expression &&
            !$node instanceof Return_ &&
            !$node instanceof Namespace_;
    }
}
