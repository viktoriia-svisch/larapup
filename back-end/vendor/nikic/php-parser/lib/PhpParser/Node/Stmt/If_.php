<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class If_ extends Node\Stmt
{
    public $cond;
    public $stmts;
    public $elseifs;
    public $else;
    public function __construct(Node\Expr $cond, array $subNodes = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->cond = $cond;
        $this->stmts = $subNodes['stmts'] ?? [];
        $this->elseifs = $subNodes['elseifs'] ?? [];
        $this->else = $subNodes['else'] ?? null;
    }
    public function getSubNodeNames() : array {
        return ['cond', 'stmts', 'elseifs', 'else'];
    }
    public function getType() : string {
        return 'Stmt_If';
    }
}
