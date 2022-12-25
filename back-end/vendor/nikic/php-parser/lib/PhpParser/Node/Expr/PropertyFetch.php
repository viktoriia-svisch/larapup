<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
class PropertyFetch extends Expr
{
    public $var;
    public $name;
    public function __construct(Expr $var, $name, array $attributes = []) {
        parent::__construct($attributes);
        $this->var = $var;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
    }
    public function getSubNodeNames() : array {
        return ['var', 'name'];
    }
    public function getType() : string {
        return 'Expr_PropertyFetch';
    }
}
