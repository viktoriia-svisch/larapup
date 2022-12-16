<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Do_ extends Node\Stmt
{
    public $stmts;
    public $cond;
    public function __construct(Node\Expr $cond, array $stmts = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->cond = $cond;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array {
        return ['stmts', 'cond'];
    }
    public function getType() : string {
        return 'Stmt_Do';
    }
}
