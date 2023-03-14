<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class DeclareDeclare extends Node\Stmt
{
    public $key;
    public $value;
    public function __construct($key, Node\Expr $value, array $attributes = []) {
        parent::__construct($attributes);
        $this->key = \is_string($key) ? new Node\Identifier($key) : $key;
        $this->value = $value;
    }
    public function getSubNodeNames() : array {
        return ['key', 'value'];
    }
    public function getType() : string {
        return 'Stmt_DeclareDeclare';
    }
}
