<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Isset_ extends Expr
{
    public $vars;
    public function __construct(array $vars, array $attributes = []) {
        parent::__construct($attributes);
        $this->vars = $vars;
    }
    public function getSubNodeNames() : array {
        return ['vars'];
    }
    public function getType() : string {
        return 'Expr_Isset';
    }
}
