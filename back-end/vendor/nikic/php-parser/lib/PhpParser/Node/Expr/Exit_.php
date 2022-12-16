<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Exit_ extends Expr
{
    const KIND_EXIT = 1;
    const KIND_DIE = 2;
    public $expr;
    public function __construct(Expr $expr = null, array $attributes = []) {
        parent::__construct($attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() : array {
        return ['expr'];
    }
    public function getType() : string {
        return 'Expr_Exit';
    }
}
