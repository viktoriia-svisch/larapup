<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Clone_ extends Expr
{
    public $expr;
    public function __construct(Expr $expr, array $attributes = []) {
        parent::__construct($attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() : array {
        return ['expr'];
    }
    public function getType() : string {
        return 'Expr_Clone';
    }
}
