<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Array_ extends Expr
{
    const KIND_LONG = 1;  
    const KIND_SHORT = 2; 
    public $items;
    public function __construct(array $items = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->items = $items;
    }
    public function getSubNodeNames() : array {
        return ['items'];
    }
    public function getType() : string {
        return 'Expr_Array';
    }
}
