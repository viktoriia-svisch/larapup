<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
class Instanceof_ extends Expr
{
    public $expr;
    public $class;
    public function __construct(Expr $expr, $class, array $attributes = []) {
        parent::__construct($attributes);
        $this->expr = $expr;
        $this->class = $class;
    }
    public function getSubNodeNames() : array {
        return ['expr', 'class'];
    }
    public function getType() : string {
        return 'Expr_Instanceof';
    }
}
