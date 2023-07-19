<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Finally_ extends Node\Stmt
{
    public $stmts;
    public function __construct(array $stmts = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array {
        return ['stmts'];
    }
    public function getType() : string {
        return 'Stmt_Finally';
    }
}