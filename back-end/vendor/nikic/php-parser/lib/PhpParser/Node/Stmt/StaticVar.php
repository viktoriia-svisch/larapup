<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Node\Expr;
class StaticVar extends Node\Stmt
{
    public $var;
    public $default;
    public function __construct(
        Expr\Variable $var, Node\Expr $default = null, array $attributes = []
    ) {
        parent::__construct($attributes);
        $this->var = $var;
        $this->default = $default;
    }
    public function getSubNodeNames() : array {
        return ['var', 'default'];
    }
    public function getType() : string {
        return 'Stmt_StaticVar';
    }
}
