<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
abstract class Cast extends Expr
{
    public $expr;
    public function __construct(Expr $expr, array $attributes = []) {
        parent::__construct($attributes);
        $this->expr = $expr;
    }
    public function getSubNodeNames() : array {
        return ['expr'];
    }
}
