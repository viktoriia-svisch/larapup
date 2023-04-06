<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
class ConstFetch extends Expr
{
    public $name;
    public function __construct(Name $name, array $attributes = []) {
        parent::__construct($attributes);
        $this->name = $name;
    }
    public function getSubNodeNames() : array {
        return ['name'];
    }
    public function getType() : string {
        return 'Expr_ConstFetch';
    }
}
