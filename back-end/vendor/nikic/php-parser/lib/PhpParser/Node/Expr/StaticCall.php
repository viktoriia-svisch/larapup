<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
class StaticCall extends Expr
{
    public $class;
    public $name;
    public $args;
    public function __construct($class, $name, array $args = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->class = $class;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
        $this->args = $args;
    }
    public function getSubNodeNames() : array {
        return ['class', 'name', 'args'];
    }
    public function getType() : string {
        return 'Expr_StaticCall';
    }
}
