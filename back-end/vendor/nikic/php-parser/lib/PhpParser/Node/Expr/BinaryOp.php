<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
abstract class BinaryOp extends Expr
{
    public $left;
    public $right;
    public function __construct(Expr $left, Expr $right, array $attributes = []) {
        parent::__construct($attributes);
        $this->left = $left;
        $this->right = $right;
    }
    public function getSubNodeNames() : array {
        return ['left', 'right'];
    }
    abstract public function getOperatorSigil() : string;
}
