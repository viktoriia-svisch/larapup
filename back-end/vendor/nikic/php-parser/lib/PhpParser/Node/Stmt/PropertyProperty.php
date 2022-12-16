<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class PropertyProperty extends Node\Stmt
{
    public $name;
    public $default;
    public function __construct($name, Node\Expr $default = null, array $attributes = []) {
        parent::__construct($attributes);
        $this->name = \is_string($name) ? new Node\VarLikeIdentifier($name) : $name;
        $this->default = $default;
    }
    public function getSubNodeNames() : array {
        return ['name', 'default'];
    }
    public function getType() : string {
        return 'Stmt_PropertyProperty';
    }
}
