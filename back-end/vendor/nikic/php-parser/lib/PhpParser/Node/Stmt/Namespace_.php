<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Namespace_ extends Node\Stmt
{
    const KIND_SEMICOLON = 1;
    const KIND_BRACED = 2;
    public $name;
    public $stmts;
    public function __construct(Node\Name $name = null, $stmts = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->name = $name;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array {
        return ['name', 'stmts'];
    }
    public function getType() : string {
        return 'Stmt_Namespace';
    }
}
