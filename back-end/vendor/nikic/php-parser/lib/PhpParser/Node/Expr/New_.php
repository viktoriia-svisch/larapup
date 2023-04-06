<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
class New_ extends Expr
{
    public $class;
    public $args;
    public function __construct($class, array $args = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->class = $class;
        $this->args = $args;
    }
    public function getSubNodeNames() : array {
        return ['class', 'args'];
    }
    public function getType() : string {
        return 'Expr_New';
    }
}
