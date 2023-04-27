<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
class FuncCall extends Expr
{
    public $name;
    public $args;
    public function __construct($name, array $args = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->name = $name;
        $this->args = $args;
    }
    public function getSubNodeNames() : array {
        return ['name', 'args'];
    }
    public function getType() : string {
        return 'Expr_FuncCall';
    }
}
