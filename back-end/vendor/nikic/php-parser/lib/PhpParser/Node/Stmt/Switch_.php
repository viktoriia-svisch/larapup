<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Switch_ extends Node\Stmt
{
    public $cond;
    public $cases;
    public function __construct(Node\Expr $cond, array $cases, array $attributes = []) {
        parent::__construct($attributes);
        $this->cond = $cond;
        $this->cases = $cases;
    }
    public function getSubNodeNames() : array {
        return ['cond', 'cases'];
    }
    public function getType() : string {
        return 'Stmt_Switch';
    }
}
