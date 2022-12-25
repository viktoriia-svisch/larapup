<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class PreInc extends Expr
{
    public $var;
    public function __construct(Expr $var, array $attributes = []) {
        parent::__construct($attributes);
        $this->var = $var;
    }
    public function getSubNodeNames() : array {
        return ['var'];
    }
    public function getType() : string {
        return 'Expr_PreInc';
    }
}
