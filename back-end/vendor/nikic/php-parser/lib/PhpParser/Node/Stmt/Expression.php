<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Expression extends Node\Stmt
{
    public $expr;
    public function __construct(Node\Expr $expr, array $attributes = []) {
        parent::__construct($attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() : array {
        return ['expr'];
    }
    public function getType() : string {
        return 'Stmt_Expression';
    }
}
