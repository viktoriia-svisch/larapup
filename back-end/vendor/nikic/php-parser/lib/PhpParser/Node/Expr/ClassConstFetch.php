<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
class ClassConstFetch extends Expr
{
    public $class;
    public $name;
    public function __construct($class, $name, array $attributes = []) {
        parent::__construct($attributes);
        $this->class = $class;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
    }
    public function getSubNodeNames() : array {
        return ['class', 'name'];
    }
    public function getType() : string {
        return 'Expr_ClassConstFetch';
    }
}
