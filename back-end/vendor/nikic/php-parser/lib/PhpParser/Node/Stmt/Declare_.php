<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Declare_ extends Node\Stmt
{
    public $declares;
    public $stmts;
    public function __construct(array $declares, array $stmts = null, array $attributes = []) {
        parent::__construct($attributes);
        $this->declares = $declares;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array {
        return ['declares', 'stmts'];
    }
    public function getType() : string {
        return 'Stmt_Declare';
    }
}
