<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class Yield_ extends Expr
{
    public $key;
    public $value;
    public function __construct(Expr $value = null, Expr $key = null, array $attributes = []) {
        parent::__construct($attributes);
        $this->key = $key;
        $this->value = $value;
    }
    public function getSubNodeNames() : array {
        return ['key', 'value'];
    }
    public function getType() : string {
        return 'Expr_Yield';
    }
}
