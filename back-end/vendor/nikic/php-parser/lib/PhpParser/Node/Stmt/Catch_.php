<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Node\Expr;
class Catch_ extends Node\Stmt
{
    public $types;
    public $var;
    public $stmts;
    public function __construct(
        array $types, Expr\Variable $var, array $stmts = [], array $attributes = []
    ) {
        parent::__construct($attributes);
        $this->types = $types;
        $this->var = $var;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array {
        return ['types', 'var', 'stmts'];
    }
    public function getType() : string {
        return 'Stmt_Catch';
    }
}
