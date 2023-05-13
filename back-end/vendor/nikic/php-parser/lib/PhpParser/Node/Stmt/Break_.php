<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Break_ extends Node\Stmt
{
    public $num;
    public function __construct(Node\Expr $num = null, array $attributes = []) {
        parent::__construct($attributes);
        $this->num = $num;
    }
    public function getSubNodeNames() : array {
        return ['num'];
    }
    public function getType() : string {
        return 'Stmt_Break';
    }
}
