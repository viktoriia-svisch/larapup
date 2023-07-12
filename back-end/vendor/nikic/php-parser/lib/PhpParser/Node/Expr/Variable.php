<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Variable extends Expr
{
    public $name;
    public function __construct($name, array $attributes = []) {
        parent::__construct($attributes);
        $this->name = $name;
    }
    public function getSubNodeNames() : array {
        return ['name'];
    }
    public function getType() : string {
        return 'Expr_Variable';
    }
}
